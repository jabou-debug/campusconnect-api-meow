<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\EventRsvp;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void {
        $admin    = User::where("role","admin")->firstOrFail();
        $faculty  = User::where("role","faculty")->first();
        $students = User::where("role","student")->get();

        $eventsData = [
            ["user_id"=>$admin->id, "title"=>"University Foundation Week 2025",
             "description"=>"Annual celebration featuring sports, cultural performances, and alumni homecoming.",
             "location"=>"Main Campus Grounds",
             "date"=>"2025-08-18","time_start"=>"08:00:00","time_end"=>"22:00:00",
             "category"=>"University Event","status"=>"upcoming",
             "capacity"=>2000,"is_free"=>true,"registration_deadline"=>"2025-08-15",
             "contact_email"=>"sao@university.edu"],
            ["user_id"=>$faculty->id, "title"=>"IT Summit 2025: AI in the Real World",
             "description"=>"Annual IT Summit with industry keynote speakers, panel discussions, live demos.",
             "location"=>"College of IT Auditorium",
             "date"=>"2025-09-22","time_start"=>"08:00:00","time_end"=>"17:00:00",
             "category"=>"Academic","status"=>"upcoming",
             "capacity"=>300,"is_free"=>true,"registration_deadline"=>"2025-09-20",
             "contact_email"=>"it.dept@university.edu"],
            ["user_id"=>$admin->id, "title"=>"Campus Job Fair 2025",
             "description"=>"Connect with recruiters from 50+ companies. Bring your resume for on-the-spot interviews.",
             "location"=>"Student Center","date"=>"2025-10-05",
             "time_start"=>"09:00:00","time_end"=>"16:00:00",
             "category"=>"Career","status"=>"upcoming",
             "capacity"=>500,"is_free"=>true,"registration_deadline"=>"2025-10-03",
             "contact_email"=>"careers@university.edu"],
        ];

        foreach ($eventsData as $data) {
            $event = Event::firstOrCreate(["title"=>$data["title"]], $data);

            // Seed RSVPs for Foundation Week (demonstrates many-to-many)
            if ($event->wasRecentlyCreated && str_contains($event->title, "Foundation")) {
                foreach ($students as $student) {
                    EventRsvp::firstOrCreate(
                        ["user_id"=>$student->id, "event_id"=>$event->id],
                        ["status"=>"confirmed","rsvp_at"=>now()]
                    );
                }
            }
        }
        $this->command->info("Events seeded with sample RSVPs.");
    }
}
