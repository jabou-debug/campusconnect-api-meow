<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void {
        $admin = User::where("role","admin")->firstOrFail();
        $announcements = [
            ["Enrollment for Second Semester Now Open",
             "All students are advised that enrollment for AY 2025-2026 Second Semester is now open. Complete your enrollment on or before August 30, 2025.",
             "Academic","high",true,"2025-08-30 23:59:59"],
            ["Campus WiFi Maintenance - August 10",
             "The campus WiFi will undergo maintenance on August 10 from 12:00 AM to 4:00 AM. Internet will be temporarily unavailable.",
             "IT Services","medium",false,"2025-08-10 04:00:00"],
            ["Foundation Week Activities - August 18-22",
             "Join us for a week-long celebration! Activities: sports festival, cultural night, academic quiz bowl, food bazaar, and alumni homecoming.",
             "Events","medium",true,"2025-08-22 23:59:59"],
            ["Scholarship Application Period Open",
             "OSS accepts scholarship applications for 2nd Semester: Academic Excellence, Financial Assistance, Sports. Submit by September 15.",
             "Financial Aid","high",false,"2025-09-15 17:00:00"],
            ["Library Hours Extended During Exam Week",
             "Library extends hours to 6AM-10PM weekdays and 8AM-6PM weekends during examination week August 25-29.",
             "Academic","low",false,"2025-08-29 23:59:59"],
        ];
        foreach ($announcements as [$title,$body,$cat,$pri,$pin,$exp]) {
            Announcement::firstOrCreate(["title" => $title], [
                "user_id"=>$admin->id, "title"=>$title, "body"=>$body,
                "category"=>$cat, "priority"=>$pri,
                "is_pinned"=>$pin, "expires_at"=>$exp,
            ]);
        }
        $this->command->info("Announcements seeded (5 records).");
    }
}
