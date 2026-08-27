<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelAnnotation;
use App\Models\Labeling\LabelExport;
use App\Models\Labeling\LabelImage;
use App\Models\Labeling\LabelLabel;
use App\Models\Labeling\LabelProject;
use App\Models\Labeling\LabelTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class DatasetExportController extends Controller
{
    public function index()
    {
        $projects = LabelProject::with(['tasks', 'labels'])->get();

        $projects->transform(function ($project) {
            $taskIds = $project->tasks->pluck('id');
            $project->total_images = LabelImage::whereIn('task_id', $taskIds)->count();
            $project->labeled_images = LabelImage::whereIn('task_id', $taskIds)->where('status', 'labeled')->count();
            $project->labels_count = $project->labels->count();
            return $project;
        });

        $exports = LabelExport::with(['project', 'user'])->where('export_type', 'image')->latest()->get();

        return view('labeling.export.index', compact('projects', 'exports'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'format' => 'required|string|in:yolo,coco,cvat,pascal_voc',
            'archive_name' => 'nullable|string|max:255',
            'include_images' => 'nullable|boolean',
        ]);

        $projectId = $request->input('project_id');
        $format = $request->input('format');
        $includeImages = $request->has('include_images');

        $project = LabelProject::findOrFail($projectId);
        $labels = LabelLabel::where('project_id', $project->id)->get();

        $taskIds = LabelTask::where('project_id', $project->id)->pluck('id');
        $images = LabelImage::whereIn('task_id', $taskIds)->get();

        $archiveName = $request->input('archive_name')
            ? str_replace(' ', '_', preg_replace('/[^A-Za-z0-9_\-]/', '', $request->input('archive_name')))
            : "dataset_{$project->id}_" . strtolower($format) . '_' . date('Ymd_His');

        $exportDir = public_path('exports/labeling');
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zipFileName = $archiveName . '.zip';
        $zipFilePath = $exportDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Không thể tạo file nén .zip!');
        }

        // Build dataset content based on format
        if ($format === 'yolo') {
            $this->buildYoloExport($zip, $project, $labels, $images, $includeImages);
        } elseif ($format === 'coco') {
            $this->buildCocoExport($zip, $project, $labels, $images, $includeImages);
        } elseif ($format === 'cvat') {
            $this->buildCvatExport($zip, $project, $labels, $images, $includeImages);
        } else {
            $this->buildPascalVocExport($zip, $project, $labels, $images, $includeImages);
        }

        $zip->close();

        $fileSize = file_exists($zipFilePath) ? filesize($zipFilePath) : 0;
        $fileUrl = asset('exports/labeling/' . $zipFileName);

        // Record in database
        LabelExport::create([
            'project_id' => $project->id,
            'export_type' => 'image',
            'format' => $format,
            'file_name' => $zipFileName,
            'file_path' => $fileUrl,
            'file_size' => $fileSize,
            'status' => 'completed',
            'created_by' => Auth::id(),
        ]);

        return response()->download($zipFilePath, $zipFileName);
    }

    public function download($id)
    {
        $export = LabelExport::findOrFail($id);
        $fileName = $export->file_name;
        $filePath = public_path('exports/labeling/' . $fileName);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Tập tin nén không tồn tại hoặc đã bị xóa!');
        }

        return response()->download($filePath, $fileName);
    }

    private function getImageDimensions($img)
    {
        $width = 1920;
        $height = 1080;
        if (!empty($img->file_path)) {
            $localPath = public_path(parse_url($img->file_path, PHP_URL_PATH));
            if (file_exists($localPath)) {
                $info = @getimagesize($localPath);
                if ($info && $info[0] > 0 && $info[1] > 0) {
                    $width = $info[0];
                    $height = $info[1];
                }
            }
        }
        return [$width, $height];
    }

    private function buildYoloExport($zip, $project, $labels, $images, $includeImages)
    {
        $classesContent = "";
        $labelMap = [];
        foreach ($labels as $idx => $lbl) {
            $classesContent .= $lbl->name . "\n";
            $labelMap[$lbl->id] = $idx;
        }
        $zip->addFromString('classes.txt', $classesContent);

        $yamlContent = "names:\n";
        foreach ($labels as $idx => $lbl) {
            $yamlContent .= "  {$idx}: \"{$lbl->name}\"\n";
        }
        $yamlContent .= "nc: " . count($labels) . "\n";
        $zip->addFromString('data.yaml', $yamlContent);

        foreach ($images as $img) {
            $annotations = LabelAnnotation::where('image_id', $img->id)->get();
            $txtContent = "";

            foreach ($annotations as $ann) {
                if (!isset($labelMap[$ann->label_id])) continue;
                $classIdx = $labelMap[$ann->label_id];
                $coords = is_array($ann->coordinates) ? $ann->coordinates : json_decode($ann->coordinates, true);

                if ($ann->annotation_type === 'polygon' && isset($coords['points']) && is_array($coords['points'])) {
                    // YOLOv8 Segmentation Format: <class> <x1> <y1> <x2> <y2> ...
                    $polyStr = [];
                    foreach ($coords['points'] as $pt) {
                        $px = $pt['x'] > 100 ? $pt['x'] / 1000 : $pt['x'] / 100;
                        $py = $pt['y'] > 100 ? $pt['y'] / 1000 : $pt['y'] / 100;
                        $polyStr[] = sprintf("%.6f %.6f", $px, $py);
                    }
                    $txtContent .= "{$classIdx} " . implode(' ', $polyStr) . "\n";
                } else {
                    // YOLO BBox Format: <class> <x_center> <y_center> <width> <height>
                    $normX = isset($coords['x']) ? ($coords['x'] > 100 ? $coords['x'] / 1000 : $coords['x'] / 100) : 0;
                    $normY = isset($coords['y']) ? ($coords['y'] > 100 ? $coords['y'] / 1000 : $coords['y'] / 100) : 0;
                    $normW = isset($coords['width']) ? ($coords['width'] > 100 ? $coords['width'] / 1000 : $coords['width'] / 100) : 0.1;
                    $normH = isset($coords['height']) ? ($coords['height'] > 100 ? $coords['height'] / 1000 : $coords['height'] / 100) : 0.1;

                    $cx = $normX + ($normW / 2);
                    $cy = $normY + ($normH / 2);

                    $txtContent .= sprintf("%d %.6f %.6f %.6f %.6f\n", $classIdx, $cx, $cy, $normW, $normH);
                }
            }

            $baseName = pathinfo($img->file_name, PATHINFO_FILENAME);
            $zip->addFromString("labels/{$baseName}.txt", $txtContent);

            if ($includeImages && !empty($img->file_path)) {
                $localImgPath = public_path(parse_url($img->file_path, PHP_URL_PATH));
                if (file_exists($localImgPath)) {
                    $zip->addFile($localImgPath, "images/{$img->file_name}");
                }
            }
        }
    }

    private function buildCocoExport($zip, $project, $labels, $images, $includeImages)
    {
        $categories = [];
        $labelMap = [];
        foreach ($labels as $idx => $lbl) {
            $catId = $idx + 1;
            $categories[] = [
                'id' => $catId,
                'name' => $lbl->name,
                'supercategory' => 'agricultural_disease'
            ];
            $labelMap[$lbl->id] = $catId;
        }

        $cocoImages = [];
        $cocoAnnotations = [];
        $annCounter = 1;

        foreach ($images as $img) {
            [$imgW, $imgH] = $this->getImageDimensions($img);
            $cocoImages[] = [
                'id' => $img->id,
                'file_name' => $img->file_name,
                'width' => $imgW,
                'height' => $imgH
            ];

            $annotations = LabelAnnotation::where('image_id', $img->id)->get();
            foreach ($annotations as $ann) {
                if (!isset($labelMap[$ann->label_id])) continue;
                $catId = $labelMap[$ann->label_id];
                $coords = is_array($ann->coordinates) ? $ann->coordinates : json_decode($ann->coordinates, true);

                if ($ann->annotation_type === 'polygon' && isset($coords['points']) && is_array($coords['points'])) {
                    $segPoints = [];
                    $xList = [];
                    $yList = [];
                    foreach ($coords['points'] as $pt) {
                        $px = round((($pt['x'] > 100 ? $pt['x'] / 1000 : $pt['x'] / 100)) * $imgW, 2);
                        $py = round((($pt['y'] > 100 ? $pt['y'] / 1000 : $pt['y'] / 100)) * $imgH, 2);
                        $segPoints[] = $px;
                        $segPoints[] = $py;
                        $xList[] = $px;
                        $yList[] = $py;
                    }
                    $xMin = min($xList);
                    $yMin = min($yList);
                    $xMax = max($xList);
                    $yMax = max($yList);
                    $w = $xMax - $xMin;
                    $h = $yMax - $yMin;

                    $cocoAnnotations[] = [
                        'id' => $annCounter++,
                        'image_id' => $img->id,
                        'category_id' => $catId,
                        'segmentation' => [$segPoints],
                        'area' => round($w * $h, 2),
                        'bbox' => [round($xMin, 2), round($yMin, 2), round($w, 2), round($h, 2)],
                        'iscrowd' => 0
                    ];
                } else {
                    $normX = isset($coords['x']) ? ($coords['x'] > 100 ? $coords['x'] / 1000 : $coords['x'] / 100) : 0;
                    $normY = isset($coords['y']) ? ($coords['y'] > 100 ? $coords['y'] / 1000 : $coords['y'] / 100) : 0;
                    $normW = isset($coords['width']) ? ($coords['width'] > 100 ? $coords['width'] / 1000 : $coords['width'] / 100) : 0.1;
                    $normH = isset($coords['height']) ? ($coords['height'] > 100 ? $coords['height'] / 1000 : $coords['height'] / 100) : 0.1;

                    $x = round($normX * $imgW, 2);
                    $y = round($normY * $imgH, 2);
                    $w = round($normW * $imgW, 2);
                    $h = round($normH * $imgH, 2);

                    $cocoAnnotations[] = [
                        'id' => $annCounter++,
                        'image_id' => $img->id,
                        'category_id' => $catId,
                        'segmentation' => [[$x, $y, $x + $w, $y, $x + $w, $y + $h, $x, $y + $h]],
                        'area' => round($w * $h, 2),
                        'bbox' => [$x, $y, $w, $h],
                        'iscrowd' => 0
                    ];
                }
            }

            if ($includeImages && !empty($img->file_path)) {
                $localImgPath = public_path(parse_url($img->file_path, PHP_URL_PATH));
                if (file_exists($localImgPath)) {
                    $zip->addFile($localImgPath, "images/{$img->file_name}");
                }
            }
        }

        $cocoData = [
            'info' => [
                'description' => "Dataset Export for {$project->name}",
                'date_created' => date('Y-m-d H:i:s')
            ],
            'categories' => $categories,
            'images' => $cocoImages,
            'annotations' => $cocoAnnotations
        ];

        $zip->addFromString('annotations.json', json_encode($cocoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function buildCvatExport($zip, $project, $labels, $images, $includeImages)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<annotations>\n";
        $xml .= "  <version>1.1</version>\n";
        $xml .= "  <meta><task><name>" . htmlspecialchars($project->name) . "</name></task></meta>\n";

        foreach ($images as $img) {
            [$imgW, $imgH] = $this->getImageDimensions($img);
            $xml .= "  <image id=\"{$img->id}\" name=\"" . htmlspecialchars($img->file_name) . "\" width=\"{$imgW}\" height=\"{$imgH}\">\n";

            $annotations = LabelAnnotation::where('image_id', $img->id)->get();
            foreach ($annotations as $ann) {
                $lbl = $labels->firstWhere('id', $ann->label_id);
                $lblTitle = htmlspecialchars($lbl ? $lbl->name : 'Unassigned');
                $coords = is_array($ann->coordinates) ? $ann->coordinates : json_decode($ann->coordinates, true);

                if ($ann->annotation_type === 'polygon' && isset($coords['points']) && is_array($coords['points'])) {
                    $pointsStrArr = [];
                    foreach ($coords['points'] as $pt) {
                        $px = round((($pt['x'] > 100 ? $pt['x'] / 1000 : $pt['x'] / 100)) * $imgW, 2);
                        $py = round((($pt['y'] > 100 ? $pt['y'] / 1000 : $pt['y'] / 100)) * $imgH, 2);
                        $pointsStrArr[] = "{$px},{$py}";
                    }
                    $pointsStr = implode(';', $pointsStrArr);
                    $xml .= "    <polygon label=\"{$lblTitle}\" occluded=\"0\" points=\"{$pointsStr}\" />\n";
                } elseif ($ann->annotation_type === 'point' && isset($coords['x']) && isset($coords['y'])) {
                    $px = round((($coords['x'] > 100 ? $coords['x'] / 1000 : $coords['x'] / 100)) * $imgW, 2);
                    $py = round((($coords['y'] > 100 ? $coords['y'] / 1000 : $coords['y'] / 100)) * $imgH, 2);
                    $xml .= "    <points label=\"{$lblTitle}\" occluded=\"0\" points=\"{$px},{$py}\" />\n";
                } else {
                    $normX = isset($coords['x']) ? ($coords['x'] > 100 ? $coords['x'] / 1000 : $coords['x'] / 100) : 0;
                    $normY = isset($coords['y']) ? ($coords['y'] > 100 ? $coords['y'] / 1000 : $coords['y'] / 100) : 0;
                    $normW = isset($coords['width']) ? ($coords['width'] > 100 ? $coords['width'] / 1000 : $coords['width'] / 100) : 0.1;
                    $normH = isset($coords['height']) ? ($coords['height'] > 100 ? $coords['height'] / 1000 : $coords['height'] / 100) : 0.1;

                    $xtl = round($normX * $imgW, 2);
                    $ytl = round($normY * $imgH, 2);
                    $xbr = round(($normX + $normW) * $imgW, 2);
                    $ybr = round(($normY + $normH) * $imgH, 2);

                    $xml .= "    <box label=\"{$lblTitle}\" occluded=\"0\" xtl=\"{$xtl}\" ytl=\"{$ytl}\" xbr=\"{$xbr}\" ybr=\"{$ybr}\" />\n";
                }
            }
            $xml .= "  </image>\n";

            if ($includeImages && !empty($img->file_path)) {
                $localImgPath = public_path(parse_url($img->file_path, PHP_URL_PATH));
                if (file_exists($localImgPath)) {
                    $zip->addFile($localImgPath, "images/{$img->file_name}");
                }
            }
        }
        $xml .= "</annotations>";

        $zip->addFromString('annotations.xml', $xml);
    }

    private function buildPascalVocExport($zip, $project, $labels, $images, $includeImages)
    {
        foreach ($images as $img) {
            [$imgW, $imgH] = $this->getImageDimensions($img);
            $xml = "<?xml version=\"1.0\"?>\n<annotation>\n";
            $xml .= "  <filename>" . htmlspecialchars($img->file_name) . "</filename>\n";
            $xml .= "  <size><width>{$imgW}</width><height>{$imgH}</height><depth>3</depth></size>\n";

            $annotations = LabelAnnotation::where('image_id', $img->id)->get();
            foreach ($annotations as $ann) {
                $lbl = $labels->firstWhere('id', $ann->label_id);
                $lblTitle = htmlspecialchars($lbl ? $lbl->name : 'Unassigned');
                $coords = is_array($ann->coordinates) ? $ann->coordinates : json_decode($ann->coordinates, true);

                if ($ann->annotation_type === 'polygon' && isset($coords['points']) && is_array($coords['points'])) {
                    $xList = [];
                    $yList = [];
                    foreach ($coords['points'] as $pt) {
                        $xList[] = round((($pt['x'] > 100 ? $pt['x'] / 1000 : $pt['x'] / 100)) * $imgW);
                        $yList[] = round((($pt['y'] > 100 ? $pt['y'] / 1000 : $pt['y'] / 100)) * $imgH);
                    }
                    $xmin = min($xList);
                    $ymin = min($yList);
                    $xmax = max($xList);
                    $ymax = max($yList);

                    $xml .= "  <object>\n";
                    $xml .= "    <name>{$lblTitle}</name>\n";
                    $xml .= "    <bndbox><xmin>{$xmin}</xmin><ymin>{$ymin}</ymin><xmax>{$xmax}</xmax><ymax>{$ymax}</ymax></bndbox>\n";
                    $xml .= "  </object>\n";
                } else {
                    $normX = isset($coords['x']) ? ($coords['x'] > 100 ? $coords['x'] / 1000 : $coords['x'] / 100) : 0;
                    $normY = isset($coords['y']) ? ($coords['y'] > 100 ? $coords['y'] / 1000 : $coords['y'] / 100) : 0;
                    $normW = isset($coords['width']) ? ($coords['width'] > 100 ? $coords['width'] / 1000 : $coords['width'] / 100) : 0.1;
                    $normH = isset($coords['height']) ? ($coords['height'] > 100 ? $coords['height'] / 1000 : $coords['height'] / 100) : 0.1;

                    $xmin = round($normX * $imgW);
                    $ymin = round($normY * $imgH);
                    $xmax = round(($normX + $normW) * $imgW);
                    $ymax = round(($normY + $normH) * $imgH);

                    $xml .= "  <object>\n";
                    $xml .= "    <name>{$lblTitle}</name>\n";
                    $xml .= "    <bndbox><xmin>{$xmin}</xmin><ymin>{$ymin}</ymin><xmax>{$xmax}</xmax><ymax>{$ymax}</ymax></bndbox>\n";
                    $xml .= "  </object>\n";
                }
            }
            $xml .= "</annotation>";

            $baseName = pathinfo($img->file_name, PATHINFO_FILENAME);
            $zip->addFromString("pascal_voc/{$baseName}.xml", $xml);

            if ($includeImages && !empty($img->file_path)) {
                $localImgPath = public_path(parse_url($img->file_path, PHP_URL_PATH));
                if (file_exists($localImgPath)) {
                    $zip->addFile($localImgPath, "images/{$img->file_name}");
                }
            }
        }
    }
}
