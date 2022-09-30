<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['status' => 'boolean', 'code' => 'string', 'extra' => 'object','input_form'=> 'object'];

    public function currencies()
    {
        return $this->hasMany(GatewayCurrency::class, 'method_code', 'code');
    }

    public function single_currency()
    {
        return $this->hasOne(GatewayCurrency::class, 'method_code', 'code')->latest();
    }

    public function scopeCrypto()
    {
        return $this->crypto == 1 ? 'crypto' : 'fiat';
    }

    public function scopeAutomatic()
    {
        return $this->where('code', '<', 1000);
    }

    public function scopeManual()
    {
        return $this->where('code', '>=', 1000);
    }
    public function scopeMethodImage()
    {
        return ($this->image) ? getImage(imagePath()['gateway']['path'] .'/' . $this->image,'800x800') : (($this->method->image) ? getImage(imagePath()['gateway']['path'] . '/' . $this->method->image,'800x800'):  asset(imagePath()['image']['default']));
    }
}
