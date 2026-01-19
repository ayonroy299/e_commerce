<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Review;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $customers = Customer::all();
        $users = User::all();

        if ($branches->isEmpty()) return;

        $countries = ['USA', 'UK', 'Canada', 'Germany', 'France', 'Australia', 'Japan', 'Bangladesh'];
        $sources = ['Google', 'Facebook', 'Direct', 'Referral', 'Email', 'Organic Search'];

        // Seed Sales for the last 12 months with varying metrics
        for ($i = 0; $i < 100; $i++) {
            $branch = $branches->random();
            $customer = $customers->random();
            $user = $users->where('branch_id', $branch->id)->first() ?? $users->random();
            
            $soldAt = now()->subDays(rand(0, 365));

            Sale::create([
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'status' => 'completed',
                'payment_status' => 'paid',
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_amount' => rand(50, 2000),
                'paid_amount' => rand(50, 2000),
                'country' => $countries[array_rand($countries)],
                'traffic_source' => $sources[array_rand($sources)],
                'sold_at' => $soldAt,
            ]);
        }

        // Seed Reviews
        $comments = [
            "Excellent product and fast delivery!",
            "The service was top-notch. Highly recommend.",
            "Items received as described, thank you.",
            "Great experience shopping here.",
            "Quality exceeded my expectations.",
            "Average experience, could be better.",
            "Super fast shipping and great support.",
            "The best store I've visited in a while.",
        ];

        foreach ($branches as $branch) {
            for ($j = 0; $j < 5; $j++) {
                Review::create([
                    'branch_id' => $branch->id,
                    'customer_name' => $customers->random()->name,
                    'rating' => rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                    'is_published' => true,
                    'source' => rand(0, 1) ? 'Landing Page' : 'Mobile App',
                ]);
            }
        }
    }
}
