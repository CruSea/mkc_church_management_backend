<?php

return [
	'mode'                  => 'utf-8',
	'format'                => 'A4',
	'author'                => '',
    'orientation'           => 'Landscape',
	'subject'               => '',
	'keywords'              => '',
	'creator'               => 'Laravel Pdf',
	'display_mode'          => 'fullpage',
	'tempDir'               => base_path('temp/'),
    'font_path' => base_path('storage/fonts/'),
    'font_data' => [
        'examplefont' => [
            'R'  => 'nyala.ttf',    // regular font
            'B'  => 'nyala.ttf',       // optional: bold font
            'I'  => 'nyala.ttf',     // optional: italic font
            'BI' => 'nyala.ttf', // optional: bold-italic font
			'useOTL' => 0xFF,
			'useKashida' => 75,
		]
		// ...add as many as you want.
	]
];
