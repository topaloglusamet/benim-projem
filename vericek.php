<?php
// HTML içeriğini URL'den al
$htmlContent = file_get_contents('https://panel.baskanonline.com/secim-oy-takip.html');

if ($htmlContent === false) {
    die("HTML içeriği alınamadı.");
}

// UTF-8 desteklemesi için meta charset ekle
$htmlContent = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . $htmlContent;

// DOMDocument kullanarak HTML içeriğini yükle
$dom = new DOMDocument;
@$dom->loadHTML($htmlContent);

// XPath kullanarak belirli 'div' içindeki tabloyu seç
$xpath = new DOMXPath($dom);
$desiredDiv = $xpath->query('//div[@style="overflow-x:auto;"]')->item(0);

if ($desiredDiv !== null) {

    $isMobile = preg_match('/Mobile|Android|BlackBerry|iPhone|Windows Phone/', $_SERVER['HTTP_USER_AGENT']);

    // Tablodaki verileri ekrana yazdır ve belirli partileri say
    if ($isMobile) {
        echo "<table border='1' style='border-collapse: collapse; width: 30%;'>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 70%;'>";
    }


    // Sadece bu 'div' içindeki tabloyu işle
    $table = $desiredDiv->getElementsByTagName('table')->item(0);
    $rows = $table->getElementsByTagName('tr');

    // Parti adlarını ve sayıları tutmak için bir dizi oluştur
    $partiler = [
        "ASİL TÜRK" => 0,
        "ALTIN OK" => 0,
        "GÖKBUDUN" => 0,
        "ÖZGÜRLÜK VE İRADE" => 0,
        "KIZILELMA" => 0,
        "YÜKSELEN GENÇLİK" => 0,
        "MİRKUT" => 0,
        "YENİLİKÇİ DEMOKRASİ" => 0,
        "BÜRKÜT" => 0
    ];

    // İttifaklar
    $ittifaklar = [
        "Diriliş İttifakı" => ["ASİL TÜRK", "ALTIN OK", "ÖZGÜRLÜK VE İRADE", "YÜKSELEN GENÇLİK"],
        "Rakip" => ["GÖKBUDUN", "YENİLİKÇİ DEMOKRASİ", "MİRKUT", "KIZILELMA", "BÜRKÜT"],
    ];

    // İttifak sonuçlarını tutmak için bir dizi
    $ittifakSonuclari = [];

    // Toplam veri sayısı
    $toplamVeriSayisi = 0;

    // Tablodaki verileri ekrana yazdır ve belirli partileri say

    echo "<thead style='background-color: #f2f2f2;'>";
    echo "<tr>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>Kullanıcı Adı</th>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>Sandık No</th>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>Katsayı</th>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>Parti</th>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>Oy Kullandığı Tarih</th>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>Vergi</th>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>Başkanlık Puanı</th>";
    echo "<th style='padding: 1px; border: 1px solid #ddd;'>SMS Onayı</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($rows as $row) {
        echo "<tr>";
        $cols = $row->getElementsByTagName('td');
        foreach ($cols as $index => $col) {
            if ($index == 5) { // Vergi sütunu (6. sütun)
                $formattedValue = number_format((float) trim($col->nodeValue), 0, '', '.');
                echo "<td style='padding: 1px; border: 1px solid #ddd;'>" . $formattedValue . "</td>";
            } elseif ($index == 6) { // Başkanlık Puanı sütunu (7. sütun)
                $formattedValue = number_format((float) trim($col->nodeValue), 0, '', '.');
                echo "<td style='padding: 1px; border: 1px solid #ddd;'>" . $formattedValue . "</td>";
            } else {
                echo "<td style='padding: 1px; border: 1px solid #ddd;'>" . htmlspecialchars($col->nodeValue) . "</td>";
            }

        }
        echo "</tr>";

        if ($cols->length > 0) {
            $toplamVeriSayisi++;
            // Satırdaki parti hücresini seç (4. hücre)
            $partiCell = $cols->item(3);
            $partiAdi = trim($partiCell->nodeValue);

            // Eğer parti adı dizide varsa, sayısını artır
            if (array_key_exists($partiAdi, $partiler)) {
                $partiler[$partiAdi]++;
            }
        }
    }
    echo "</tbody>";
    echo "</table>";

    // İttifak sonuçlarını hesapla
    foreach ($ittifaklar as $ittifakAdi => $partiListesi) {
        $ittifakToplami = 0;
        foreach ($partiListesi as $parti) {
            if (array_key_exists($parti, $partiler)) {
                $ittifakToplami += $partiler[$parti];
            }
        }
        $ittifakSonuclari[$ittifakAdi] = $ittifakToplami;
    }

    $isMobile = preg_match('/Mobile|Android|BlackBerry|iPhone|Windows Phone/', $_SERVER['HTTP_USER_AGENT']);

    // Tablodaki verileri ekrana yazdır ve belirli partileri say
    if ($isMobile) {
        echo "<div style='position: absolute; top: 0; right: 0; padding: 1px; font-weight: bold; text-align:center;'>";

    } else {
        echo "<div style='position: absolute; top: 0; right: 0; padding: 1px; font-weight: bold;'>";

    }


   

    // Toplam veri sayısını yazdır
    echo "Toplamda " . $toplamVeriSayisi . " veri bulundu.<br><br>";

    foreach ($partiler as $partiAdi => $partiSayisi) {
        echo $partiAdi . " partisine ait " . $partiSayisi . " veri bulundu.<br>";
    }

    // İttifak sonuçlarını da yazdır
    echo "<br>";
    foreach ($ittifakSonuclari as $ittifakAdi => $ittifakSayisi) {
        echo $ittifakAdi . " toplamda " . $ittifakSayisi . " veri bulundurdu.<br>";
    }
    echo "</div>";
} else {
    echo "Belirtilen 'div' bulunamadı.";
}
?>