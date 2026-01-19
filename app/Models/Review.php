<?php
 
 namespace App\Models;
 
 use App\Models\Traits\BelongsToBranch;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\SoftDeletes;
 use Spatie\Activitylog\LogOptions;
 use Spatie\Activitylog\Traits\LogsActivity;
 
 class Review extends BaseModel
 {
     use HasFactory, SoftDeletes, BelongsToBranch, LogsActivity;
 
     protected $fillable = [
         'branch_id',
         'customer_name',
         'rating',
         'comment',
         'is_published',
         'source',
     ];
 
     protected $casts = [
         'rating' => 'integer',
         'is_published' => 'boolean',
     ];
 
     public function getActivitylogOptions(): LogOptions
     {
         return LogOptions::defaults()
             ->logFillable()
             ->logOnlyDirty();
     }
 }
 
