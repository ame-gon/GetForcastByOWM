<?php

if ($argc < 2)
{
    print_usage();
    return;
}

// jsonファイルから設定情報の読み込み
$config_file = $argv[1];
//指定したファイルの要素をすべて取得する
$config_json = file_get_contents($config_file);
//json形式のデータを連想配列の形式にする
$config_data = json_decode($config_json, true);

// 設定情報のパラメータ設定チェック
if ( check_config_params($config_data) == -1 ) {
    echo "Missing parameters in configuration file.\n";
    return;
}

// 固有値設定
// API サーバーのURL
$api_url = $config_data['api_url'];
// 情報取得する都市の lat
$lat = $config_data['lat'];
// 情報取得する都市の lon
$lon = $config_data['lon'];
// API で情報取得する際に設定する exclude
$exclude = $config_data['exclude'];
// API で情報取得する際に設定する units
$units = $config_data['units'];
// API で情報取得するデータの種類(気温、湿度、気圧など)
$target_data = $config_data['target_data'];
// API サーバーで発行された appid
$appid = $config_data['appid'];
// API で取得した予報データを書き込むファイル
$data_file = $config_data['data_file'];
// 予報データを送信する Zabbix サーバー
$zabbix_ip = $config_data['zabbix_ip'];
// Zabbix で予報データを格納するアイテムキー
$zabbix_item_key = $config_data['zabbix_item_key'];

// 生成した時間データを使用して、指定した直近時間のセンサー計測値を取得する。
$query = ['lat'=>$lat,'lon'=>$lon,'exclude'=>$exclude,'units'=>$units,'appid'=>$appid];

// API でデータ取得
$response_json = file_get_contents($api_url . http_build_query($query));

// 結果は json 形式で返されるので配列に変換
$array_result = json_decode($response_json,true);

// デバッグ用
// var_dump($array_result);

// 取得したデータ中の 24 時間後のデータを取得
// (取得したデータには取得時間から丸2日間分のデータが含まれている)
$array_count = count($array_result);
if ($array_count > 0) {
    // 24時間後のデータ配列を取得
    $array_one_result = $array_result["hourly"][24];
    $unixtime = $array_one_result["dt"];
    $forcast_pressure = $array_one_result[$target_data];

    // デバッグ用
    // var_dump("");
    // var_dump("------------------------------");
    // var_dump($unixtime);
    // var_dump($forcast_pressure);

    file_put_contents($data_file, "OpenWeatherMap " . $zabbix_item_key . " " . $unixtime . " " . $forcast_pressure . "\n");

    // Zabbix に送信
    $cmd = 'zabbix_sender -z ' . $zabbix_ip . ' -T -i ' . $data_file;
    exec($cmd, $opt);

} else {
    echo "no data...\n";
}

// === 関数 ===
/*
    スクリプトの使い方表示
*/
function print_usage()
{
    echo "This is a getiing sensor data script.\n";
    echo "  $ php getForcastPressure.php <path of config>\n";
}

/*
    設定ファイルのパラメータチェック

    パラメータに不足なし：return 0
    パラメータに不足あり：return -1
*/
function check_config_params($config)
{
    if( isset($config['api_url']) && isset($config['lat']) &&
        isset($config['lon']) && isset($config['exclude']) &&
        isset($config['units']) && isset($config['target_data']) &&
        isset($config['appid']) && isset($config['data_file']) &&
        isset($config['zabbix_ip']) && isset($config['zabbix_item_key']) ) {
            
        return 0;
    } else {
        return -1;
    }
}

?>
