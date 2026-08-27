<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account\Role;
use App\Models\User;
use App\Models\Farm\Garden;
use App\Models\Account\SystemSetting;
use App\Models\Chatbot\ChatbotKnowledgeBase;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\Device;
use App\Models\Iot\CameraMedia;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles (3 tác nhân chính theo usecase)
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Quản trị viên']
        );

        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            ['name' => 'Nhà quản lý']
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'Người dùng']
        );

        // 2. Demo Users for 3 roles (Pass: 123456)
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Supderadmin',
                'phone' => '0987654321',
                'email' => 'supderadmin@admin.com',
                'password' => Hash::make('123456'),
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]
        );

        $manager = User::firstOrCreate(
            ['username' => 'manager'],
            [
                'name' => 'Manager',
                'phone' => '0987654322',
                'email' => 'manager@manager.com',
                'password' => Hash::make('123456'),
                'role_id' => $managerRole->id,
                'status' => 'active',
            ]
        );

        $farmer = User::firstOrCreate(
            ['username' => 'farmer'],
            [
                'name' => 'Nguyễn Văn A',
                'phone' => '0987654323',
                'email' => 'nguyenvana@gmail.com',
                'password' => Hash::make('123456'),
                'role_id' => $userRole->id,
                'status' => 'active',
            ]
        );

        $nguyenvanb = User::firstOrCreate(
            ['username' => 'nguyenvanb'],
            [
                'name' => 'Nguyễn Văn B',
                'phone' => '0987654324',
                'email' => 'nguyenvand@gmail.com',
                'password' => Hash::make('123456'),
                'role_id' => $userRole->id,
                'status' => 'active',
            ]
        );

        // 3. Demo Gardens
        $gardenPhucHoa = Garden::firstOrCreate(
            ['code' => 'PH-01'],
            [
                'user_id' => $farmer->id,
                'name' => 'Vườn Vải Phúc Hòa',
                'crop_type' => 'Vải',
                'area_m2' => null,
                'location' => 'Phúc Hòa, Bắc Ninh',
                'latitude' => 21.3856310,
                'longitude' => 106.1586140,
                'status' => 'active',
            ]
        );

        $gardenLucNgan = Garden::firstOrCreate(
            ['code' => 'LN-01'],
            [
                'user_id' => $farmer->id,
                'name' => 'Vườn Vải Lục Ngạn',
                'crop_type' => 'Vải',
                'area_m2' => null,
                'location' => 'Lục Ngạn, Bắc Ninh',
                'latitude' => 21.3925040,
                'longitude' => 106.6747570,
                'status' => 'active',
            ]
        );

        // 4. Demo Monitoring Stations
        $station_phuc_hoa_01 = MonitoringStation::firstOrCreate(
            ['code' => 'TPH-01'],
            [
                'garden_id' => $gardenPhucHoa->id,
                'name' => 'Trạm Phúc Hòa Số 1',
                'latitude' => 21.3853110,
                'longitude' => 106.1619620,
                'status' => 'active',
                'data_interval' => 60,
            ]
        );

        MonitoringStation::firstOrCreate(
            ['code' => 'TPH-02'],
            [
                'garden_id' => $gardenPhucHoa->id,
                'name' => 'Trạm Phúc Hòa Số 2',
                'latitude' => 21.3857110,
                'longitude' => 106.1653090,
                'status' => 'active',
                'data_interval' => 60,
            ]
        );

        MonitoringStation::firstOrCreate(
            ['code' => 'TLN-02'],
            [
                'garden_id' => $gardenLucNgan->id,
                'name' => 'Trạm Lục Ngạn Số 2',
                'latitude' => 21.3917050,
                'longitude' => 106.6801210,
                'status' => 'active',
                'data_interval' => 60,
            ]
        );

        MonitoringStation::firstOrCreate(
            ['code' => 'TLN-01'],
            [
                'garden_id' => $gardenLucNgan->id,
                'name' => 'Trạm Lục Ngạn Số 1',
                'latitude' => 21.3921040,
                'longitude' => 106.6847560,
                'status' => 'active',
                'data_interval' => 60,
            ]
        );

        // 5. Demo Camera Device
        $cameraDevice = Device::firstOrCreate(
            ['code' => 'CAM-TT-01'],
            [
                'monitoring_station_id' => $station_phuc_hoa_01->id,
                'name' => 'Camera PTZ Trạm Số 1',
                'type' => 'camera',
                'status' => 'active',
            ]
        );

        // 6. Demo Camera Media
        CameraMedia::firstOrCreate(
            ['name' => 'CAM_TT01_20260814_063000.jpg'],
            [
                'device_id' => $cameraDevice->id,
                'type' => 'image',
                'file_path' => 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=400',
            ]
        );

        CameraMedia::firstOrCreate(
            ['name' => 'CAM_TT01_20260814_060000.jpg'],
            [
                'device_id' => $cameraDevice->id,
                'type' => 'image',
                'file_path' => 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=400',
            ]
        );

        CameraMedia::firstOrCreate(
            ['name' => 'REC_TT01_20260814_0600.mp4'],
            [
                'device_id' => $cameraDevice->id,
                'type' => 'video',
                'file_path' => 'uploads/videos/sample.mp4',
            ]
        );

        // 7. System Settings
        $settings = [
            'system_name' => 'Hệ Thống IoT Nông Nghiệp & Cảnh Báo Sâu Bệnh Tỉnh Bắc Ninh',
            'organization' => 'Sở Khoa Học và Công Nghệ Tỉnh Bắc Ninh',
            'hotline' => '1800 6888',
            'admin_email' => 'khcn@bacninh.gov.vn',
            'data_send_interval' => 60,
            'monitoring_system_active' => 1,
            'copyright' => '© 2026 Hệ Thống IoT Nông Nghiệp Bắc Ninh. Bảo lưu mọi quyền.',
        ];

        foreach ($settings as $key => $val) {
            SystemSetting::firstOrCreate(['key' => $key], ['value' => $val]);
        }

        // 8. Chatbot Knowledge Base Samples (UC 58)
        ChatbotKnowledgeBase::firstOrCreate(
            ['intent' => 'downy_mildew_treatment'],
            [
                'question_pattern' => 'Cây vải bị đốm vàng dưới lá có lớp mốc trắng là bệnh gì và trị như thế nào?',
                'answer' => 'Đây là triệu chứng bệnh sương mai (Downy Mildew) do nấm Pseudoperonospora cubensis gây ra. Khuyến cáo phun thuốc trừ nấm gốc Metalaxyl + Mancozeb hoặc Nano Bạc Đồng 500ppm vào lúc sáng sớm khi ráo sương.',
                'status' => 'active',
            ]
        );

        ChatbotKnowledgeBase::firstOrCreate(
            ['intent' => 'stem_borer_prevention'],
            [
                'question_pattern' => 'Khi nào cần phun thuốc trừ sâu đục cuống quả vải?',
                'answer' => 'Nên tiến hành phun thuốc sinh học BT hoặc bao trái khi tổng nhiệt tích lũy GDD đạt 480-500 độ ngày, trước khi sâu non tuổi 2 đục sâu vào cuống quả.',
                'status' => 'active',
            ]
        );

        // 9. Default Care Categories
        $defaultCategories = ['Tưới nước', 'Bón phân', 'Phun thuốc BVTV', 'Tỉa cành & Làm cỏ', 'Chăm sóc khác'];
        foreach ($defaultCategories as $idx => $catName) {
            \App\Models\Farm\CareCategory::firstOrCreate(
                ['name' => $catName],
                [
                    'user_id' => $admin->id,
                    'description' => 'Danh mục công việc ' . $catName,
                    'sort_order' => $idx + 1,
                ]
            );
        }

        // 10. AI Labeling Subsystem Demo Data (UC 47)
        $project = \App\Models\Labeling\LabelProject::firstOrCreate(
            ['name' => 'Dự Án Nhận Dạng Sâu Bệnh Cây Trồng 2026'],
            [
                'description' => 'Tập dữ liệu huấn luyện mô hình YOLOv8 & ResNet nhận dạng bệnh sương mai và sâu đục cuống trên cây vải & bưởi',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        // Demo Labels
        $label1 = \App\Models\Labeling\LabelLabel::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sâu đục cuống quả'],
            ['color' => '#ef4444', 'description' => 'Tổn thương do sâu đục cuống cuộn quanh quả']
        );

        $label2 = \App\Models\Labeling\LabelLabel::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Bệnh sương mai'],
            ['color' => '#f59e0b', 'description' => 'Đốm mốc màu sương trên mặt dưới lá']
        );

        $label3 = \App\Models\Labeling\LabelLabel::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Đốm đen / Mốc lá'],
            ['color' => '#8b5cf6', 'description' => 'Vết bệnh đốm tròn viền đen lá']
        );

        $label4 = \App\Models\Labeling\LabelLabel::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Vùng lá khỏe mạnh'],
            ['color' => '#10b981', 'description' => 'Mô lá xanh bình thường không nhiễm bệnh']
        );

        // Demo Task
        $task = \App\Models\Labeling\LabelTask::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Gán nhãn bộ ảnh khảo sát đợt 1 (Vườn Vải & Bưởi)'],
            [
                'description' => 'Tập hợp 4 ảnh nông nghiệp chụp thực địa cần khoanh vùng nhận dạng',
                'status' => 'in_progress',
                'assignee_id' => $manager->id,
                'created_by' => $admin->id,
            ]
        );

        // Demo Job
        $job = \App\Models\Labeling\LabelJob::firstOrCreate(
            ['task_id' => $task->id],
            [
                'assignee_id' => $manager->id,
                'status' => 'in_progress',
                'stage' => 'annotation',
                'progress' => 25,
                'started_at' => now(),
            ]
        );

        // Demo Images
        $imagesData = [
            [
                'file_name' => 'vai_phuc_hoa_suong_mai_01.jpg',
                'file_path' => asset('uploads/labeling/images/vai_phuc_hoa_suong_mai_01.jpg'),
                'width' => 1200,
                'height' => 800,
                'status' => 'unlabeled',
            ],
            [
                'file_name' => 'vai_luc_ngan_sau_duc_cuong_02.jpg',
                'file_path' => asset('uploads/labeling/images/vai_luc_ngan_sau_duc_cuong_02.jpg'),
                'width' => 1200,
                'height' => 800,
                'status' => 'unlabeled',
            ],
            [
                'file_name' => 'buoi_thuan_thanh_dom_den_03.jpg',
                'file_path' => asset('uploads/labeling/images/buoi_thuan_thanh_dom_den_03.jpg'),
                'width' => 1200,
                'height' => 800,
                'status' => 'unlabeled',
            ],
            [
                'file_name' => 'la_khoe_thuc_dia_04.jpg',
                'file_path' => asset('uploads/labeling/images/la_khoe_thuc_dia_04.jpg'),
                'width' => 1200,
                'height' => 800,
                'status' => 'unlabeled',
            ],
        ];

        foreach ($imagesData as $img) {
            \App\Models\Labeling\LabelImage::firstOrCreate(
                ['task_id' => $task->id, 'file_name' => $img['file_name']],
                $img
            );
        }

        // Demo Text NLP Project
        $textProject = \App\Models\Labeling\LabelTextProject::firstOrCreate(
            ['name' => 'Dự Án Trích Xuất Thực Thể Chatbot Nông Nghiệp (NER 2026)'],
            [
                'description' => 'Gán nhãn các thực thể tên như Bệnh Cây Trồng, Sâu Hại, Thuốc BVTV, Vùng Canh Tác từ văn bản hỏi đáp nông dân',
                'task_type' => 'ner',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        // Demo Text Labels
        $textLabelsData = [
            ['name' => 'Bệnh Cây Trồng', 'color' => '#ef4444', 'description' => 'Tên các loại bệnh cây trồng như Sương mai, Đốm đen'],
            ['name' => 'Sâu Hại', 'color' => '#f59e0b', 'description' => 'Tên các loại côn trùng sâu hại như Sâu đục cuống, Rệp sáp'],
            ['name' => 'Thuốc BVTV', 'color' => '#10b981', 'description' => 'Tên hóa chất bảo vệ thực vật như Ridomil Gold, Matyl'],
            ['name' => 'Vùng Canh Tác', 'color' => '#8b5cf6', 'description' => 'Địa danh huyện xã nông nghiệp Bắc Ninh như Lục Ngạn, Phúc Hòa'],
        ];

        foreach ($textLabelsData as $tl) {
            \App\Models\Labeling\LabelTextLabel::firstOrCreate(
                ['project_id' => $textProject->id, 'name' => $tl['name']],
                $tl
            );
        }

        // Demo Text Task
        $textTask = \App\Models\Labeling\LabelTextTask::firstOrCreate(
            ['project_id' => $textProject->id, 'name' => 'Nhiệm vụ gán nhãn câu hỏi phòng trừ sâu bệnh đợt 1'],
            [
                'description' => 'Tập hợp 3 văn bản hướng dẫn kỹ thuật phòng sâu bệnh vải thiều & bưởi',
                'status' => 'in_progress',
                'assignee_id' => $manager->id,
                'created_by' => $admin->id,
            ]
        );

        // Demo Text Documents
        $textDocsData = [
            [
                'title' => 'Hướng dẫn phòng trừ bệnh sương mai trên cây vải thiều Phúc Hòa',
                'content' => 'Bệnh sương mai hại vải là một trong những dịch hại nguy hiểm nhất tại xã Phúc Hòa, huyện Lục Ngạn. Khi phát hiện chồi non xuất hiện đốm nâu xám, bà con cần phun ngay thuốc Ridomil Gold 68WG hoặc Matyl 72WP theo đúng liều lượng khuyến cáo.',
                'status' => 'unlabeled',
            ],
            [
                'title' => 'Kỹ thuật diệt sâu đục cuống quả vải tại vùng Lục Ngạn',
                'content' => 'Trạm quan trắc cảnh báo sâu đục cuống quả phát triển mạnh vào giai đoạn vải vào đường. Để bảo vệ năng suất tại Lục Ngạn, bà con chú ý cắt tỉa cành tạo tán và sử dụng bẫy pheromone kết hợp thuốc bảo vệ thực vật sinh học.',
                'status' => 'unlabeled',
            ],
            [
                'title' => 'Tư vấn xử lý bệnh đốm đen trên vườn bưởi Diễn Thuận Thành',
                'content' => 'Vườn bưởi Thuận Thành đang gặp hiện tượng lá bị nấm đốm đen tấn công. Đề nghị nhà vườn phun thuốc ngừa gốc đồng và bổ sung vi lượng kali để tăng sức đề kháng cho cây.',
                'status' => 'unlabeled',
            ],
        ];

        foreach ($textDocsData as $doc) {
            \App\Models\Labeling\LabelTextDocument::firstOrCreate(
                ['task_id' => $textTask->id, 'title' => $doc['title']],
                $doc
            );
        }

        // Demo RAG Knowledge Base
        $kb = \App\Models\Labeling\LabelKnowledgeBase::firstOrCreate(
            ['name' => 'Cơ Sở Tri Thức Nông Nghiệp Tỉnh Bắc Ninh 2026'],
            [
                'description' => 'Kho dữ liệu tổng hợp quy trình kỹ thuật canh tác VietGAP, sổ tay phòng trừ dịch hại sâu bệnh bưởi & vải',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        $kbDocs = [
            [
                'title' => 'Quy trình kỹ thuật canh tác vải thiều Lục Ngạn chuẩn VietGAP',
                'content' => 'Quy trình canh tác vải thiều Lục Ngạn chuẩn VietGAP đòi hỏi tuân thủ nghiêm ngặt thời điểm tưới tiêu, bón phân hữu cơ sinh học và cắt tỉa cành đợt 1 sau thu hoạch. Việc ghi chép nhật ký nông hộ đảm bảo tính truy xuất nguồn gốc nông sản xuất khẩu.',
            ],
            [
                'title' => 'Sổ tay phân biệt dịch hại sương mai và sâu đục cuống trên cây vải',
                'content' => 'Bệnh sương mai gây hại lây lan qua bào tử nấm khi độ ẩm không khí vượt 85%. Sâu đục cuống quả gây hại trực tiếp vào phần cuống làm rụng quả hàng loạt. Cần kết hợp trạm quan trắc tự động và bẫy dính để dự báo sớm dịch hại.',
            ],
            [
                'title' => 'Hướng dẫn bón phân và tưới tiêu tự động cho vườn bưởi Diễn Thuận Thành',
                'content' => 'Hệ thống tưới nhỏ giọt kết hợp bón phân qua đường ống giúp tiết kiệm 40% lượng nước và nâng cao độ đường Brix của bưởi Diễn Thuận Thành. Định kỳ kiểm tra độ PH của đất ở mức 6.0 - 6.5 để rễ cây hấp thụ dinh dưỡng tốt nhất.',
            ],
        ];

        foreach ($kbDocs as $dData) {
            $docRecord = \App\Models\Labeling\LabelKnowledgeDocument::firstOrCreate(
                ['knowledge_base_id' => $kb->id, 'title' => $dData['title']],
                [
                    'content' => $dData['content'],
                    'source_type' => 'manual_entry',
                    'status' => 'active',
                ]
            );

            // Auto-chunking into sentence chunks for testing RAG Vector Store
            $text = $dData['content'];
            $sentences = explode('. ', $text);
            foreach ($sentences as $idx => $s) {
                $trimmed = trim($s);
                if (!empty($trimmed)) {
                    \App\Models\Labeling\LabelKnowledgeChunk::firstOrCreate(
                        ['document_id' => $docRecord->id, 'chunk_text' => $trimmed],
                        [
                            'content' => $trimmed,
                            'token_count' => str_word_count($trimmed),
                            'vector_id' => 'vec_' . $docRecord->id . '_' . ($idx + 1),
                            'status' => 'indexed',
                        ]
                    );
                }
            }
        }
    }
}
