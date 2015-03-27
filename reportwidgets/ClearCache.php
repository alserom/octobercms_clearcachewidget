<?php namespace Romanov\ClearCacheWidget\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

class ClearCache extends ReportWidgetBase
{
    protected $defaultAlias = 'romanov_clear_cache';
	
    public function render(){
        $this->vars['size'] = $this->getSizes();
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
        ];
    }

    public function onClear(){
        \Artisan::call('cache:clear');
        \Flash::success(e(trans('romanov.clearcachewidget::lang.plugin.success')));
        $widget = ($this->property("nochart"))? 'widget2' : 'widget';
        return [
            'partial' => $this->makePartial($widget, ['size' => $this->getSizes()])
        ];
    }

    private function get_dir_size($directory) {
        if(count(scandir($directory)) == 2)    return 0;
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

        $ccache = $this->get_dir_size(storage_path().'/cms/cache');
        $s['ccache'] = $this->format_size($ccache);
        $ccombiner = $this->get_dir_size(storage_path().'/cms/combiner');
        $s['ccombiner'] = $this->format_size($ccombiner);
        $ctwig = $this->get_dir_size(storage_path().'/cms/twig');
        $s['ctwig'] = $this->format_size($ctwig);
        $fcache = $this->get_dir_size(storage_path().'/framework/cache');
        $s['fcache'] = $this->format_size($fcache);
        $s['all'] = $this->format_size($ccache + $ccombiner + $ctwig + $fcache);
        return $s;
    }

}
