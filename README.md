福岡市オープンデータ多言語化MOD
==========
このプログラムは「公共施設等情報のオープンデータ実証 開発者サイト」が提供するデータをMicrosoft Translatorを使用して多言語対応します。  
しかしながら機械翻訳は完全ではないため、不適切なメッセージかもしれません。この場合、Twitterのアカウントにログインすることで、その不適切なメッセージを修正できます。  
今回は、現在提供されている福岡市のオープンデータにおいて、外国からの観光客でも利用する可能性があると思われる病院と避難所のデータを多言語化しています。  

福岡市オープンデータ多言語化MOD  
http://needtec.sakura.ne.jp/fukuoka_map  

共施設等情報のオープンデータ実証 開発者サイト  
http://teapot.bodic.org/  

国土数値情報取得プログラム  
https://github.com/mima3/kokudo  


インストール方法
-----------------
1.Gitよりコードを取得して、Webサーバーに配置します。  

    git clone git://github.com/mima3/fukuoka_map.git
    cp -rf fukuoka_map /home/xxx/www/

2.必要なディレクトリを作成します。  

    cd /home/xxx/www/fukuoka_map/
    mkdir logs
    mkdir cache
    mkdir compiled

3.composerにより依存ファイルのインストールを行います。  

    cd /home/xxx/www/fukuoka_map/
    php ~/composer.phar self-update 
    php ~/composer.phar install

4.default.htaccessを参考に.htaccessを作成します。  

5.config.php.defaultを参考にconfig.phpを作成します。  
この際、以下のように、非公開の領域のconfig.phpを参照するようにWebサーバー中のconfig.phpを指定するといいでしょう。

    <?php
    require_once '/home/xxx/private/config.php';

ライセンス
-------------
当方が作成したコードに関してはMITとします。  
その他、jqueryなどに関しては、それぞれにライセンスを参照してください。

    The MIT License (MIT)

    Copyright (c) 2015 m.ita

    Permission is hereby granted, free of charge, to any person obtaining a copy of
    this software and associated documentation files (the "Software"), to deal in
    the Software without restriction, including without limitation the rights to
    use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
    the Software, and to permit persons to whom the Software is furnished to do so,
    subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
    FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
    COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
    IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

