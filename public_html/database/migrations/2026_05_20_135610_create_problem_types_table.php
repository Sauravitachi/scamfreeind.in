<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('problem_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
        });

        $now = now();
        $problemTypes = [
            "Cyber Crime",
            "SEBI Matters",
            "Divorce & Child Custody",
            "Property & Real Estate",
            "Cheque Bounce & Money Recovery",
            "Employment Issues",
            "Consumer Protection",
            "Civil Matters",
            "Company & Start-Ups",
            "Other Legal Problem",
            "Criminal Matter",
            "MSME Recovery & MSME Related Matter",
            "RERA Consultation",
            "Muslim Law",
            "Debt Recovery Tribunal Matters",
            "Banking Loan Recovery Issues",
            "Bank Account Freeze",
            "Patent",
            "Trademark",
            "Copyright",
            "Intellectual Property Rights",
            "CBI Related Matters",
            "NDPS Matters",
            "Service Matters",
            "CAT Matters",
            "Arbitration Law",
            "Board of Revenue",
            "NCDRC Consumer Cases",
            "Insolvency & Bankruptcy",
            "Media Law & IP Infringements",
            "Supreme Court Matters",
            "High Court Matters",
            "Inheritance & Will",
            "Sexual Harassment at Workplace",
            "FDI Matters",
            "NCLT Matters",
            "NCLAT Matters",
            "IBC Related Matters",
            "Liquidation Related Matters",
            "RBI Related Matters",
            "Cryptocurrency Issues",
            "Startup Legal",
            "ESOP Legal",
            "Fund Raising Legal",
            "Corporate Governance",
            "Business Management",
            "Immigration & VISA",
            "HR Legal Issues",
            "Salary Non Payment",
            "Employment Termination",
            "GST",
            "Service Tax",
            "Excise Duty",
            "SFIO Matters",
            "Traffic Challan"
        ];

        foreach ($problemTypes as $index => $type) {
            DB::table('problem_types')->insert([
                'slug' => Str::slug($type, '_'),
                'title' => $type,
                'is_default' => ($index === 0) ? true : false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_types');
    }
};
