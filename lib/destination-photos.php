<?php

function ts_destination_photos(): array
{
    return [
        'Nueva York' => 'https://images.unsplash.com/photo-1546436836-07a91091f160?auto=format&fit=crop&w=1600&q=78',
        'París' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1600&q=78',
        'Barcelona' => 'https://images.unsplash.com/photo-1583422409516-2895a77efded?auto=format&fit=crop&w=1600&q=78',
        'Dubai' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1600&q=78',
        'Kioto' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1600&q=78',
        'Roma' => 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=1600&q=78',
        'Santorini' => 'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&w=1600&q=78',
        'Río de Janeiro' => 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?auto=format&fit=crop&w=1600&q=78',
        'Cusco' => 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?auto=format&fit=crop&w=1600&q=78',
        'Miami' => 'https://images.unsplash.com/photo-1506966953602-c20cc11f75e3?auto=format&fit=crop&w=1600&q=78',
        'Buenos Aires' => 'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?auto=format&fit=crop&w=1600&q=78',
        'Cartagena' => 'https://images.unsplash.com/photo-1583531352515-8884af319dc1?auto=format&fit=crop&w=1600&q=78',
        'Mendoza' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?auto=format&fit=crop&w=1600&q=78',
    ];
}

function ts_guess_destination_photo(string $destination): ?string
{
    $photos = ts_destination_photos();
    foreach ($photos as $key => $url) {
        if (mb_stripos($destination, $key) !== false) {
            return $url;
        }
    }
    return null;
}
