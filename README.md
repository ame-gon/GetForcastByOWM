# OpenWetherMap の API を使用して取得したデータを Zabbix に送信して監視する

## LICENSE
This software is released under the MIT License, see LICENSE.txt.

## 概要
OpenWetherMap の API で提供されている予報データを取得して Zabbix に送信するスクリプトです。  
本スクリプトでは指定した都市の 24 時間後の天気データを OpenWeathreMap API で取得後 Zabbix に送信しています。  

## 構成
こんな感じです。  

```
[OpenWetherMap] <=== API ===> [本スクリプト設置の PC等] === Zabbix Sender ===> [Zabbix]
```

## 要件
- 本スクリプト設置の PC等
    - OpenWetherMap と HTTP 通信できること
    - Git、PHP が動作する環境であること
        - 参考：稼働確認済OSとモジュールバージョン
            - OS：CentOS 7
            - Git：1.8.3.1-22.el7_8
            - PHP：7.2.34
- Zabbix
    - Zabbix Sender はインストールしておく
    - 参考：稼働確認済OSとモジュールバージョン
        - OS：CentOS 7
        - Zabbix：5.0.25

## 使い方
### 事前準備
- OpenWetherMap で api 使用のための appid を取得する
- 予報データを取得したい都市の緯度(lattitude)、軽度(longitude)を調べる
- Zabbixサーバーを準備して、予報データを保存するアイテムを作成する
    - アイテムのタイプは Zabbix トラッパー
    - キー に設定した文字列はあとで使用するのでメモしておく
- 本リポジトリを Zabbix と通信できる環境に clone する
- clone してきたファイルに含まれている config.json.org をコピーして config.json を作成する
- config.json を編集して必要な値を設定する
    - 例：横浜の気圧情報を取得する際に設定してみた
    ```
        "api_url" : "https://api.openweathermap.org/data/2.5/onecall?",
        "lat" : "35.44",
        "lon" : "139.63",
        "exclude" : "current,minutely,daily,alerts",
        "units" : "metric",
        "target_data" : "pressure",
        "appid" : "OpenWetherMap から発行された appid",
        "data_file" : "取得したデータを一時的に書き込むファイル",
        "zabbix_ip" : "ZabbixサーバーのIP",
        "zabbix_item_key" : "Zabbixサーバーに作成したアイテムのキー"
    ```
    - target_data は temp(気温) や humidity(湿度) なども取得することができる。
        - 取得できそうな値は OpenWetherMap で調べてみてください。

### 実行方法
API の利用登録や config.json の準備ができたら、以下のコマンドを実行する。  
  
```
php <スクリプトが保存されているパス>/getForcast.php <config.jsonが保存されているパス>/config.json
```
  
スクリプトが正常に実行されれば Zabbix の監視アイテムに24時間後の気圧の値が記録されると思います。  
※ Zabbix では未来のデータとしての扱いになるので、データの見方は気を付けてください。  

## 免責事項
- 本スクリプトはあくまでも自己責任でご利用ください。
- OpenWetherMap API を使用するために必要なユーザー登録等はご自身で行ってください。
- OpenWetherMap の仕様変更やサービスの変更などを含む本スクリプトを使用して発生したあらゆる損害、損失などについては保障しません。
