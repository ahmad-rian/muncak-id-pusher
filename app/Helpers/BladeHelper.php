<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('routeActive')) {
  function routeActive($paths = [])
  {
    $currentSegment = Request::segment(2);

    if (empty($paths) && Request::is('admin')) return 'btn-active';
    if (is_string($paths)) return $currentSegment === $paths ? 'btn-active' : '';
    if (is_array($paths)) return in_array($currentSegment, $paths) ? 'btn-active' : '';

    return '';
  }
}
