<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void {
        $student = User::where("email","student@university.edu")->firstOrFail();
        $schedules = [
            ["Monday","IT321","Mobile Computing","Prof. Maria Santos","IT Lab 3","07:30:00","09:00:00","Lecture/Laboratory",3,"BSIT-3A"],
            ["Monday","IT322","Web Systems","Prof. Jose Reyes","IT Lab 1","09:00:00","10:30:00","Lecture/Laboratory",3,"BSIT-3A"],
            ["Monday","GE101","Purposive Communication","Prof. Ana Dizon","Room 201","13:00:00","14:30:00","Lecture",3,"BSIT-3A"],
            ["Tuesday","IT323","Information Assurance","Prof. Carlos Mendoza","Room 305","10:30:00","12:00:00","Lecture",3,"BSIT-3A"],
            ["Wednesday","IT321","Mobile Computing","Prof. Maria Santos","IT Lab 3","07:30:00","09:00:00","Lecture/Laboratory",3,"BSIT-3A"],
            ["Thursday","IT325","Capstone Project 1","Prof. Maria Santos","IT Lab 4","13:00:00","16:00:00","Laboratory",3,"BSIT-3A"],
            ["Friday","IT322","Web Systems","Prof. Jose Reyes","IT Lab 1","09:00:00","10:30:00","Laboratory",3,"BSIT-3A"],
        ];
        foreach ($schedules as [$day,$code,$name,$instr,$room,$start,$end,$type,$units,$sec]) {
            Schedule::firstOrCreate(
                ["user_id"=>$student->id,"subject_code"=>$code,"day"=>$day],
                ["user_id"=>$student->id,"subject_code"=>$code,"subject_name"=>$name,
                 "instructor"=>$instr,"room"=>$room,"day"=>$day,
                 "time_start"=>$start,"time_end"=>$end,"type"=>$type,
                 "units"=>$units,"section"=>$sec,
                 "semester"=>"1st","school_year"=>"2025-2026"]
            );
        }
        $this->command->info("Schedules seeded (7 class slots).");
    }
}
