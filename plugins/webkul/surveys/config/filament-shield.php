<?php

use Webkul\Survey\Filament\Resources\SurveyResource;

$basic = ['view_any', 'view', 'create', 'update'];
$delete = ['delete', 'delete_any'];
$forceDelete = ['force_delete', 'force_delete_any'];
$restore = ['restore', 'restore_any'];

return [
    'resources' => [
        'manage' => [
            SurveyResource::class => [...$basic, ...$delete, ...$restore, ...$forceDelete],
        ],
    ],
];
