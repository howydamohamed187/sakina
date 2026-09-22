<?php

use App\Support\ContactTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('kind');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        $types = [];

        foreach (ContactTypes::all() as $index => $kind) {
            $types[] = [
                'name' => ContactTypes::label($kind),
                'kind' => $kind,
                'status' => 'active',
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('contact_types')->insert($types);

        Schema::table('contact_channels', function (Blueprint $table) {
            $table->foreignId('contact_type_id')
                ->nullable()
                ->after('name')
                ->constrained('contact_types')
                ->nullOnDelete();
        });

        $typeIds = DB::table('contact_types')->pluck('id', 'kind');

        foreach ($typeIds as $kind => $id) {
            DB::table('contact_channels')
                ->where('type', $kind)
                ->update(['contact_type_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('contact_channels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_type_id');
        });

        Schema::dropIfExists('contact_types');
    }
};
