<?php namespace Romanov\ClearCacheWidget;

use System\Classes\PluginBase;
use Lang;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            'name'        => 'romanov.clearcachewidget::lang.plugin.name',
            'description' => 'romanov.clearcachewidget::lang.plugin.description',
            'author'      => 'Alexander Romanov',
            'icon'        => 'icon-trash'
        ];
    }

    public function registerReportWidgets()
    {
        return [
            'Romanov\ClearCacheWidget\ReportWidgets\ClearCache' => [
                'label'   => 'romanov.clearcachewidget::lang.plugin.name',
                'context' => 'dashboard'
            ]
        ];
    }

}
