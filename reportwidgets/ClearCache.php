<?php namespace Romanov\ClearCacheWidget\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

class ClearCache extends ReportWidgetBase
{
    protected $defaultAlias = 'romanov_clear_cache';
	
    public function render(){
        $this->vars['size'] = $this->getSizes();
        $this->vars['radius'] = $this->property("radius");
        $widget = ($this->property("nochart"))? 'widget2' : 'widget';
        return $this->makePartial($widget);
    }

    public function defineProperties()
    {
        return [
            'title' => [
                'title'             => 'backend::lang.dashboard.widget_title_label',
                'default'           => 'romanov.clearcachewidget::lang.plugin.name',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'nochart' => [
                'title'             => 'romanov.clearcachewidget::lang.plugin.nochart',
                'type'              => 'checkbox',
            ],
            'radius' => [
                'title'             => 'romanov.clearcachewidget::lang.plugin.radius',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Only numbers!',
                'default'           => '200',
            ],
            'delthumbs' => [
                'title'             => 'romanov.clearcachewidget::lang.plugin.delthumbs',
                'type'              => 'checkbox',
                'default'           => false,
            ],
			'thumbspath' => [
                'title'             => 'romanov.clearcachewidget::lang.plugin.delthumbspath',
                'type'              => 'string',
                'placeholder'       => "/app/uploads/public",
            ],
        ];
    }

    public function onClear(){
        \Artisan::call('cache:clear');
        if($this->property("delthumbs"))    $this->delThumbs();
        \Flash::success(e(trans('romanov.clearcachewidget::lang.plugin.success')));
        $widget = ($this->property("nochart"))? 'widget2' : 'widget';
        return [
            'partial' => $this->makePartial($widget, ['size' => $this->getSizes(),'radius' => $this->property("radius")])
        ];
    }

    private function get_dir_size($directory) {
        if(!file_exists($directory) || count(scandir($directory)) <= 2) {
            return 0;
        }
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    private function format_size($size) {
        $mod = 1024;
        $units = explode(' ','B KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    private function getSizes(){

        $s['ccache_b'] = $this->get_dir_size(storage_path().'/cms/cache');
        $s['ccache'] = $this->format_size($s['ccache_b']);
        $s['ccombiner_b'] = $this->get_dir_size(storage_path().'/cms/combiner');
        $s['ccombiner'] = $this->format_size($s['ccombiner_b']);
        $s['ctwig_b'] = $this->get_dir_size(storage_path().'/cms/twig');
        $s['ctwig'] = $this->format_size($s['ctwig_b']);
        $s['fcache_b'] = $this->get_dir_size(storage_path().'/framework/cache');
        $s['fcache'] = $this->format_size($s['fcache_b']);
        $s['all'] = $this->format_size($s['ccache_b'] + $s['ccombiner_b'] + $s['ctwig_b'] + $s['fcache_b']);
        return $s;
    }

    private function delThumbs(){
        $thumbs = array();
        $path = storage_path();
		$tp = $this->property('thumbspath');
        $path .= empty($tp) ? "/app/uploads/public" : $tp;
        $iterator = new \RecursiveDirectoryIterator($path);
        foreach (new \RecursiveIteratorIterator($iterator) as $file) {
            if(preg_match("/^thumb_\w+_crop.*/", $file->getFilename())){
                $thumbs[] = $file->getRealPath();
            }
        }
        foreach($thumbs as $img){
            unlink($img);
        }
    }

}
