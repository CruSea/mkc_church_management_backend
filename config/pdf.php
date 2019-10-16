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
	'tempDir'               => base_path('../temp/'),
    'font_path' => base_path('storage/fonts/'),
    'font_data' => [
        'examplefont' => [
            'R'  => 'Nyala.ttf',    // regular font
            'B'  => 'Nyala.ttf',       // optional: bold font
            'I'  => 'Nyala.ttf',     // optional: italic font
            'BI' => 'Nyala.ttf', // optional: bold-italic font
			'useOTL' => 0xFF,
			'useKashida' => 75,
		]
		// ...add as many as you want.
	]
];
