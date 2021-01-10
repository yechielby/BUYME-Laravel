<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'isCompleted', 'isShared',
    ];


    /**
     * The function defines the many-to-many relation
     */
    public function users() {
        //return $this->belongsToMany(RelatedModel, pivot_table_name, foreign_key_of_current_model_in_pivot_table, foreign_key_of_other_model_in_pivot_table);
        return $this->belongsToMany( Trop::class, 'task__users', 'task_id' , 'user_id' );
    }
}
