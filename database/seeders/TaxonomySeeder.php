<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // Institute Types
        $types = [
            ['name' => 'School', 'slug' => 'school'],
            ['name' => 'Madrasa', 'slug' => 'madrasa'],
            ['name' => 'College', 'slug' => 'college'],
            ['name' => 'University', 'slug' => 'university'],
            ['name' => 'Kindergarten', 'slug' => 'kindergarten'],
            ['name' => 'Vocational Institute', 'slug' => 'vocational-institute'],
            ['name' => 'Polytechnic', 'slug' => 'polytechnic'],
            ['name' => 'Cadet College', 'slug' => 'cadet-college'],
        ];
        
        foreach ($types as $i => $type) {
            DB::table('institute_types')->updateOrInsert(
                ['slug' => $type['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $type['name'],
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Languages (ISO 639-1)
        $languages = [
            ['code' => 'bn', 'name' => 'Bengali', 'native_name' => 'বাংলা'],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية'],
            ['code' => 'ur', 'name' => 'Urdu', 'native_name' => 'اردو'],
        ];
        foreach ($languages as $lang) {
            DB::table('languages')->updateOrInsert(
                ['code' => $lang['code']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $lang['name'],
                    'native_name' => $lang['native_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Categories (top-level educational categories) - Restructured
        $categories = [
            ['name' => 'Government', 'slug' => 'government', 'icon' => 'building-library'],
            ['name' => 'Private', 'slug' => 'private', 'icon' => 'building-office'],
            ['name' => 'English Medium', 'slug' => 'english-medium', 'icon' => 'globe'],
            ['name' => 'English Version', 'slug' => 'english-version', 'icon' => 'book-open'],
            ['name' => 'Bangla Medium', 'slug' => 'bangla-medium', 'icon' => 'book'],
            ['name' => 'Alia Madrasa', 'slug' => 'alia-madrasa', 'icon' => 'graduation-cap'],
            ['name' => 'Qawmi Madrasa', 'slug' => 'qawmi-madrasa', 'icon' => 'mosque'],
            ['name' => 'Cadet', 'slug' => 'cadet', 'icon' => 'shield'],
            ['name' => 'Technical & Vocational', 'slug' => 'technical-vocational', 'icon' => 'cog'],
        ];
        foreach ($categories as $i => $cat) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Curriculums
        $curriculums = [
            'National Curriculum (Bangladesh)', 'Cambridge International', 'Edexcel International',
            'IB Diploma Programme', 'Dawra-e-Hadith (Qawmi)', 'Alia Madrasa Curriculum',
            'Technical & Vocational', 'BTEB',
        ];
        foreach ($curriculums as $c) {
            DB::table('curriculums')->updateOrInsert(
                ['slug' => Str::slug($c)],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $c,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Education Boards
        $boards = [
            ['name' => 'Board of Intermediate and Secondary Education, Chattogram', 'short_name' => 'Chattogram Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Dhaka', 'short_name' => 'Dhaka Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Rajshahi', 'short_name' => 'Rajshahi Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Cumilla', 'short_name' => 'Cumilla Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Barishal', 'short_name' => 'Barishal Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Sylhet', 'short_name' => 'Sylhet Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Jashore', 'short_name' => 'Jashore Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Dinajpur', 'short_name' => 'Dinajpur Board'],
            ['name' => 'Board of Intermediate and Secondary Education, Mymensingh', 'short_name' => 'Mymensingh Board'],
            ['name' => 'Bangladesh Madrasah Education Board', 'short_name' => 'Madrasah Board'],
            ['name' => 'Bangladesh Technical Education Board', 'short_name' => 'BTEB'],
            ['name' => 'Bangladesh Open University', 'short_name' => 'BOU'],
        ];
        foreach ($boards as $b) {
            DB::table('education_boards')->updateOrInsert(
                ['slug' => Str::slug($b['short_name'])],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $b['name'],
                    'short_name' => $b['short_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Programs (grade levels and programs)
        $programs = [
            ['name' => 'Playgroup', 'program_type' => 'grade_level'],
            ['name' => 'Nursery', 'program_type' => 'grade_level'],
            ['name' => 'KG-I', 'program_type' => 'grade_level'],
            ['name' => 'KG-II', 'program_type' => 'grade_level'],
            ['name' => 'Class 1', 'program_type' => 'grade_level'],
            ['name' => 'Class 2', 'program_type' => 'grade_level'],
            ['name' => 'Class 3', 'program_type' => 'grade_level'],
            ['name' => 'Class 4', 'program_type' => 'grade_level'],
            ['name' => 'Class 5', 'program_type' => 'grade_level'],
            ['name' => 'Class 6', 'program_type' => 'grade_level'],
            ['name' => 'Class 7', 'program_type' => 'grade_level'],
            ['name' => 'Class 8', 'program_type' => 'grade_level'],
            ['name' => 'Class 9', 'program_type' => 'grade_level'],
            ['name' => 'Class 10 (SSC)', 'program_type' => 'grade_level'],
            ['name' => 'Class 11', 'program_type' => 'grade_level'],
            ['name' => 'Class 12 (HSC)', 'program_type' => 'grade_level'],
            ['name' => 'Ebtedayee', 'program_type' => 'grade_level'],
            ['name' => 'Mutawassitah', 'program_type' => 'grade_level'],
            ['name' => 'Dakhil', 'program_type' => 'grade_level'],
            ['name' => 'Alim', 'program_type' => 'grade_level'],
            ['name' => 'Fazil', 'program_type' => 'grade_level'],
            ['name' => 'Kamil', 'program_type' => 'grade_level'],
            ['name' => 'Hifzul Quran', 'program_type' => 'islamic_study'],
            ['name' => 'Nazera', 'program_type' => 'islamic_study'],
        ];
        foreach ($programs as $i => $p) {
            DB::table('programs')->updateOrInsert(
                ['slug' => Str::slug($p['name'])],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $p['name'],
                    'program_type' => $p['program_type'],
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Facility Groups
        $facilityGroups = [
            ['name' => 'Academic Facilities', 'slug' => 'academic', 'icon' => 'book'],
            ['name' => 'Sports Facilities', 'slug' => 'sports', 'icon' => 'running'],
            ['name' => 'Infrastructure', 'slug' => 'infrastructure', 'icon' => 'building'],
            ['name' => 'Transportation', 'slug' => 'transportation', 'icon' => 'bus'],
            ['name' => 'Health & Safety', 'slug' => 'health-safety', 'icon' => 'heartbeat'],
        ];
        foreach ($facilityGroups as $i => $fg) {
            $groupId = DB::table('facility_groups')->updateOrInsert(
                ['slug' => $fg['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $fg['name'],
                    'icon' => $fg['icon'],
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Facilities (actual items)
        $facilityItems = [
            'academic' => ['Library', 'Computer Lab', 'Science Lab', 'Language Lab', 'Smart Classroom'],
            'sports' => ['Playground', 'Indoor Games Room', 'Swimming Pool', 'Gymnasium'],
            'infrastructure' => ['Auditorium', 'Canteen', 'Parking', 'Generator/UPS', 'CCTV'],
            'transportation' => ['School Bus', 'Van Service'],
            'health-safety' => ['First Aid Room', 'Fire Extinguisher', 'Boundary Wall'],
        ];
        
        $groups = DB::table('facility_groups')->get()->keyBy('slug');
        foreach ($facilityItems as $groupSlug => $items) {
            if (!isset($groups[$groupSlug])) continue;
            
            $groupId = $groups[$groupSlug]->id;
            foreach ($items as $item) {
                DB::table('facilities')->updateOrInsert(
                    ['slug' => Str::slug($item)],
                    [
                        'uuid' => (string) Str::uuid(),
                        'facility_group_id' => $groupId,
                        'name' => $item,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
        
        // Fee Types
        $feeTypes = [
            ['name' => 'Monthly Tuition', 'slug' => 'monthly-tuition', 'fee_category' => 'recurring'],
            ['name' => 'Admission Fee', 'slug' => 'admission-fee', 'fee_category' => 'one_time'],
            ['name' => 'Session Fee', 'slug' => 'session-fee', 'fee_category' => 'one_time'],
            ['name' => 'Exam Fee', 'slug' => 'exam-fee', 'fee_category' => 'recurring'],
            ['name' => 'Transport Fee', 'slug' => 'transport-fee', 'fee_category' => 'recurring'],
            ['name' => 'Annual Development Fee', 'slug' => 'annual-development-fee', 'fee_category' => 'one_time'],
            ['name' => 'Books & Stationery', 'slug' => 'books-stationery', 'fee_category' => 'student_expense'],
            ['name' => 'Uniform Fee', 'slug' => 'uniform-fee', 'fee_category' => 'student_expense'],
            ['name' => 'Lab Fee', 'slug' => 'lab-fee', 'fee_category' => 'recurring'],
            ['name' => 'Hostel/Boarding Fee', 'slug' => 'hostel-fee', 'fee_category' => 'recurring'],
            ['name' => 'Caution Money (Refundable)', 'slug' => 'caution-money', 'fee_category' => 'one_time'],
        ];
        foreach ($feeTypes as $ft) {
            DB::table('fee_types')->updateOrInsert(
                ['slug' => $ft['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $ft['name'],
                    'fee_category' => $ft['fee_category'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
