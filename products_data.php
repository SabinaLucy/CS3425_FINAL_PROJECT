<?php
// products_data.php
// A centralized map to store matching images and descriptions without touching the DB schema.

// NOTE: You must create an 'images/' folder and place your downloaded .jpg files there, 
// named exactly like the IDs below (e.g. 1001.jpg).

$product_metadata = [
    // Electronics
    1001 => [
        'img' => 'images/1001.avif',
        'alt_img' => 'images/1001_alt.jpg', // Optional second image for details page
        'description' => 'A premium mechanical gaming mouse built for durability and high-speed performance. Housed in a brushed aircraft-grade aluminum frame, it’s designed to withstand a lifetime of gaming while staying lightweight.'
    ],
    1002 => [
        'img' => 'images/1002.jpg', // 
        'alt_img' => 'images/1002_alt.jpg',
        'description' => 'An elite, ultra-lightweight wireless gaming mouse designed for professional-level performance.'
    ],
    1003 => [
        'img' => 'images/1003.jpg', 
        'alt_img' => 'images/1003_alt.jpg',
        'description' => 'A top-tier gaming mouse pad that merges the legendary performance of the QcK micro-woven surface with dynamic dual-zone RGB lighting.' 
    ],
    1004 => [
        'img' => 'images/1004.jpg',
        'alt_img' => 'images/1004_alt.jpg',
        'description' => 'A high-performance desktop processor widely regarded as one of the best CPUs for gaming due to its advanced 3D V-Cache technology.'
    ],
    1005 => [
        'img' => 'images/1005.avif', 
        'alt_img' => 'images/1005_alt.jpg',
        'description' => 'a high-end graphics card built on the Ada Lovelace architecture.'
    ],
    1006 => [
        'img' => 'images/1006.avif', 
        'alt_img' => 'images/1006_alt.jpg',
        'description' => ' The "sweet spot" powerhouse designed to squeeze every frame out of your Ryzen 7800X3D. It’s a high-end motherboard that refuses to compromise, offering flagship-tier power delivery and next-gen speeds without the "X670E" price premium.'
    ],
    1007 => [
        'img' => 'images/1007.png', 
        'alt_img' => 'images/1007_alt.jpg',
        'description' => 'The marathon runner of gaming headsets, legendary for its industry-leading 300-hour battery life. One charge literally lasts for weeks of gaming.'
    ],
    
    // Peripherals
    1008 => [
        'img' => 'images/1008.webp', 
        'alt_img' => 'images/1008_alt.jpg',
        'description' => 'The undisputed king of silence, blending world-class noise cancellation with a sleek, "noiseless" design. It doesn’t just play music; it creates a private sanctuary wherever you go.'
    ],
    1009 => [
        'img' => 'images/1009.avif',
        'alt_img' => 'images/1009_alt.jpg',
        'description' => 'A curved gaming masterpiece designed to wrap your field of vision in pure speed. It’s the world’s first 1000R curved monitor, matching the human eye for total immersion and zero distortion.'
    ],
    1010 => [
        'img' => 'images/1010.jpg',
        'alt_img' => 'images/1010_alt.jpg',
        'description' => 'A high-octane powerhouse built to dominate the digital battlefield. With a redesigned chassis and a display that pulls you into the action, it’s the ultimate choice for gamers who refuse to play on "Low" settings.'
    ]
];