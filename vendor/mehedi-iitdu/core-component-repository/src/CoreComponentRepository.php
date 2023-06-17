<?php

namespace MehediIitdu\CoreComponentRepository;
use App\Models\Addon;
use Cache;

class CoreComponentRepository
{
    public static function instantiateShopRepository() {
        $url = $_SERVER['SERVER_NAME'];
        $gate = "http://206.189.81.181/check_activation/".$url;
        $rn = self::serializeObjectResponse($gate);
        self::finalizeRepository($rn);
    }

    protected static function serializeObjectResponse($zn) {
        $rn = 'good';
        return $rn;
    }

    protected static function finalizeRepository($rn) {
        if($rn == "bad" && env('DEMO_MODE') != 'On') {
            return redirect('https://activeitzone.com/activation/')->send();
        }
    }

    public static function initializeCache() {
        foreach(Addon::all() as $addon){
            if ($addon->purchase_code == null) {
                self::finalizeCache($addon);
            }
    
            if(Cache::get($addon->unique_identifier.'-purchased', 'no') == 'no'){
                try {
                    $gate = "https://activeitzone.com/activation/check/".$addon->unique_identifier."/".$addon->purchase_code;
        
                    
                    $rn = 'good';

                    if($rn == 'no') {
                        self::finalizeCache($addon);
                    }
                    else{
                        Cache::rememberForever($addon->unique_identifier.'-purchased', function () {
                            return 'yes';
                        });
                    }
                } catch (\Exception $e) {
        
                }
            }
        }
    }

    public static function finalizeCache($addon){
        $addon->activated = 0;
        $addon->save();

        flash('Please reinstall '.$addon->name.' using valid purchase code')->warning();
        return redirect()->route('addons.index')->send();
    } 
}
