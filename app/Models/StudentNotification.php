<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNotification extends Model
{
    protected $primaryKey = 'notification_id';
    
    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];
    
    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
    /**
     * Get the order associated with the notification
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }
    
    /**
     * Scope to get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Scope to get recent notifications
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
