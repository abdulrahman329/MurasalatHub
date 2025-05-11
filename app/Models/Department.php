<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name', // اسم القسم
    ];

    public $timestamps = false; // تعطيل الطوابع الزمنية التلقائية

    /**
     * العلاقة بين القسم والمستخدمين.
     * القسم يحتوي على العديد من المستخدمين.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class); // قسم يحتوي على العديد من المستخدمين
    }

    /**
     * العلاقة للرسائل المرسلة من هذا القسم.
     * القسم يمكنه إرسال العديد من المراسلات.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentCorrespondences()
    {
        return $this->hasMany(Correspondence::class, 'sender_department_id');
    }

    /**
     * العلاقة للرسائل المستلمة من هذا القسم.
     * القسم يمكنه استقبال العديد من المراسلات.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedCorrespondences()
    {
        return $this->hasMany(Correspondence::class, 'receiver_department_id');
    }
}
