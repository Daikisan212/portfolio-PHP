    <?php
    //phpのど頭
    //htmlが一文字でもセットされているとエラーになります
    session_name('sesname');//セッションの名前を決める、無くても平気(PHPSESSIDなどのデフォルトになる)
    session_start();//必須、セッションか使える様になる
    session_regenerate_id(true);//無くても平気だかセキュリティ的にあったほうがいい。

    //書き込みファイル
    $savefile = "log/portfolio.txt";
    //カウントする変数初期化
    //htmlのなまえ
    $templatefile = "portfolio.html";

    $cnt = array();
    // 既にファイルがあれば読み込む

    if(file_exists($savefile))
    {
        $cnt = file($savefile);
    }

    if(isset($_GET["del"]) && is_numeric($_GET["del"]))
    {
        $delhen = $_GET["del"];   
        if(isset($cnt[$delhen]))
        {
            $cnt[$delhen] = "削除\n";
            file_put_contents($savefile, $cnt);
        }
    }

        //$error = "";
        $log = "";
        //$msgg = "";
        if(!isset($_SESSION['error']))
        {
            $_SESSION['error'] = "";
            
        }
        if(!isset($_SESSION['msgg']))
        {
            $_SESSION['msgg'] = "";
        }
        //$name = "";
        //$_SESSION['name'];がセットされていなかったら
        //$_SESSION['name'];をからの文字列に
        if(!isset($_SESSION['name']))
        {
            $_SESSION['name']= "";
        }
        
        if(isset($_POST["nm"]) && isset($_POST["msg"]))
        {
            //$name
            $_SESSION['name'] = $_POST["nm"];
            $_SESSION['msg'] = $_POST["msg"];
           
            if($_POST["nm"] == "" || $_POST["msg"] == "")
            {
                $_SESSION['error'] = "エラーだよ";

            }
            else
            {

                //fileの初期化して
                $OnceFile=$_FILES['upfile'];
                $nameTemp = "";
                if($OnceFile['name']!=='')
                {
                    //ファイル名作成
                    //$NameTemp=$OnceFile['name'];//元の名前にする場合(元のファイル名がどうしても必要な場合)
                    $NameTemp=md5($OnceFile['name'].microtime());//ユニークな英数字にする場合(こちらのほうが被らなくて良い)
                    //アップロードされたファイルを指定されたパスと名前で保存
                    move_uploaded_file($OnceFile['tmp_name'], 'img/'.$NameTemp);


                    //ここ
                    $SrcFileName='img/'.$NameTemp;//元画像       ファイル名     file先file名'img/'.$NameTemp
                    $DstFileName='img/'.$NameTemp."_s";//書き出す画像ファイル名

                    //画像の情報を取得
                    $ImgData=getimagesize($SrcFileName);

                    //自作関数ImageCreateFromType()を使って画像タイプに合わせてソースファイルをメモリに読みこむ
                    $SrcImg=ImageCreateFromType($SrcFileName,$ImgData);

                        //サイズ計算部分、元画像の縦横で大きい方を100にする
                        if($ImgData[0]>$ImgData[1])
                        {
                            $DstSize[0]=100;
                            $DstSize[1]=(int)($ImgData[1]/$ImgData[0]*100);
                        }
                        else
                        {
                            $DstSize[0]=(int)($ImgData[0]/$ImgData[1]*100);
                            $DstSize[1]=100;
                        }
                        //書き込む画像をメモリ上に作る(まだ空の画像)
                        $DstImg=imagecreatetruecolor($DstSize[0],$DstSize[1]);

                        //元画像からリサイズしながら、書き込む画像にコピーする
                        ImageCopyResampled($DstImg,$SrcImg,0,0, 0,0, $DstSize[0],$DstSize[1], $ImgData[0], $ImgData[1]);

                        //自作関数ImageWrite()を使って画像タイプに合わせて画像かきだし
                        ImageWrite($DstImg,$DstFileName,$ImgData[2]);

                        //最後に使った画像をメモリから消す
                        imagedestroy($SrcImg);
                        imagedestroy($DstImg);          
                }

                //↓ここに改行コード
                //nameの取得
                //保存
                $msg=str_replace(array("\r\n","\r","\n"),'<br>',htmlspecialchars($_POST["msg"]));
                //
                $cnt[]= htmlspecialchars($_POST["nm"])."<>".$msg."<>".date('Y/m/d H:i:s')."<>".$NameTemp."\n";
                file_put_contents($savefile, $cnt);
                $_SESSION['msgg'] = "";
            }
            //自分自身にgetでつなぎなおす
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        //$_GET['p']がセットされていないか、数字じゃないか、1よりちいさければ
        //$_GET['p']を1にする

        if(!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] < 1)
        {
            $_GET['p'] = 1;
        }
        // $一ページの表示量の変数を5に
        $OnceView = 5;
        // $いま表示しようとしているページを表示する場合に飛ばすログの数の変数を、計算式で求め入れる 
             //たとえば1p5個で1pなら最初(0個目)から、2p目だったら5個目から、3pだったら10個目からと計算できる式をつくる
             $jumpLog = ($_GET['p'] - 1) * $OnceView;
        // $飛ばすログをカウントする変数を0に
        $jumpLogCount = 0;
        // $表示するをカウントする変数を0に
        $ViewLogCount = 0;
        // $次に表示するものがあるかどうかの変数をfalseに
        $NextLog = false;

        
        //$cnt++; // 1つだけ加算
        foreach($cnt as $k=>$v)
        {

            if($v =="削除\n")
                continue;
            
            $date = explode("<>",$v);
            if(isset($_GET["selname"]) && $_GET["selname"]!= $date[0])
                continue;
             
            //$飛ばすログをカウントする変数が$いま表示しようとしているページを表示する場合に飛ばすログの数の変数よりちいさければ
            if($jumpLogCount < $jumpLog)
            {
                //$飛ばすログをカウントする変数をプラス1
                $jumpLogCount++;
                continue;
            }
                    
            //$表示するをカウントする変数が$一ページの表示量の変数よりおおければ
            if($ViewLogCount >= $OnceView)
            {
                //$次に表示するものがあるかどうかの変数をtrueに
                $NextLog = true;
                //ブレイク
                break;
            }
            
            //$表示するをカウントする変数をプラス1
            $ViewLogCount++;
            
           
            
            
            /*echo $date[0]."<br>";
            echo $date[2]."<br>";
            echo $date[3]."<br>";*/
            $imgTag = "";
            $imgName=trim($date[3]);

            if($imgName!="")
            {
                $imgTag = '<a href="img/'.$imgName.'"><img src="img/'.$imgName.'_s"></a>'; //画像を分ける！ 　右がオリジナルで左がコピー
            }

           // if(!isset($_GET["selname"]) || $_GET["selname"]==$date[0])
            //{
                //logと表示
               $log .= '<p><span style="font-weight:bold"><a href="?selname='.urlencode($date[0]).'">'.$date[0].'</a></span>　
                <span style="font-size:10px;">'.$date[2].'</span><a href="?del='.$k.'">削除</a><br><div 
                style="margin-left:1em;">'.$date[1].'</div>'.$imgTag.'</p>';
           // }
            
             
        }
        $pagelink = [];
        //ゲットのpが2以上だったら
        if($_GET['p'] >= 2)
        {
            //前に戻るaタグを変数にいれる
            $PreviousP = $_GET['p'] - 1;
            $pagelink[] = '<a href="?p='.$PreviousP.'">&lt&lt前のページ</a>';
        }
        if($NextLog)
        {
            $NextP = $_GET['p'] + 1;
            $pagelink[] = '<a href="?p='.$NextP.'">次のページ&gt;&gt;</a>';
        }

        $HtmlData=file_get_contents($templatefile);//読み込み

        

       // $HtmlData=str_replace('{{置き換え部分1}}','置き換えたよ！',$HtmlData);
        $HtmlData=str_replace('{{名前}}',$_SESSION['name'],$HtmlData);
        $HtmlData=str_replace('{{メッセージ}}',$_SESSION['msgg'],$HtmlData);
        $HtmlData=str_replace('{{エラー}}',$_SESSION['error'],$HtmlData);
        $HtmlData=str_replace('{{ログ}}',$log,$HtmlData);
        $HtmlData=str_replace('{{ページリンク}}','<p>'.implode('｜',$pagelink).'</p>',$HtmlData);
        $HtmlData=str_replace($selname,'selected="selected"',$HtmlData);
        
        //正規表現で残った{{何か}}を消す 余っているものも消す！
        $HtmlData=preg_replace("/{{.*?}}/","",$HtmlData);

        echo $HtmlData;
        $_SESSION['error'] = "";

        
       //元画像からタイプに合わせてイメージ作成
function ImageCreateFromType($Imgname,$ImgData)
{
	switch($ImgData[2])
	{
		case IMAGETYPE_GIF:
			return imagecreatefromgif($Imgname);
		case IMAGETYPE_JPEG:
		case IMAGETYPE_JPC:
		case IMAGETYPE_JP2:
		case IMAGETYPE_JPX:
		case IMAGETYPE_JB2:
		case IMAGETYPE_JPEG2000:
			return imagecreatefromjpeg($Imgname);
		case IMAGETYPE_PNG:
			return imagecreatefrompng($Imgname);
		case IMAGETYPE_BMP;
		case IMAGETYPE_WBMP:
			return imagecreatefromwbmp($Imgname);
		case IMAGETYPE_XBM:
			return imagecreatefromxbm($Imgname);
		case IMAGETYPE_SWF:
		case IMAGETYPE_SWC:
		case IMAGETYPE_PSD:
		case IMAGETYPE_TIFF_II:
		case IMAGETYPE_TIFF_MM:
		case IMAGETYPE_IFF:
		default:
			return false;//エラー
	}
}


//タイプから適当な関数を探して書き出す
function ImageWrite($DstImg,$NewName,$Type)
{
	switch($Type)
	{
		case IMAGETYPE_GIF:
			return imagegif($DstImg,$NewName);
		case IMAGETYPE_JPEG:
		case IMAGETYPE_JPC:
		case IMAGETYPE_JP2:
		case IMAGETYPE_JPX:
		case IMAGETYPE_JB2:
		case IMAGETYPE_JPEG2000:
			return imagejpeg($DstImg,$NewName);
		case IMAGETYPE_PNG:
			return imagepng($DstImg,$NewName);
		case IMAGETYPE_BMP;
		case IMAGETYPE_WBMP:
			return imagewbmp($DstImg,$NewName);
		case IMAGETYPE_XBM:
		case IMAGETYPE_SWF:
		case IMAGETYPE_SWC:
		case IMAGETYPE_PSD:
		case IMAGETYPE_TIFF_II:
		case IMAGETYPE_TIFF_MM:
		case IMAGETYPE_IFF:
		default:
			return false;//エラー
    }
    
}
          
?>
