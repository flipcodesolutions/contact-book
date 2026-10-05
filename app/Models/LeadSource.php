<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    protected $fillable = [
        'name',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Reuse a source with the same name, or add a new one from the Other box.
     */
    public static function storeName($name): int
    {
        $name = trim((string) $name);
        $sources = self::query()->orderBy('name')->get();

        foreach ($sources as $source) {
            if (strtolower($source->name) === strtolower($name)) {
                return $source->id;
            }
        }

        $source = new self;
        $source->name = $name;
        $source->save();

        return $source->id;
    }
}
