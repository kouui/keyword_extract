<?php
$dsn = 'mysql:host=localhost;dbname=mydb;charset=utf8';
$db_user = 'root';
$db_pass = '';
$driver_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $driver_options);
} catch (PDOException $e) {
    exit('データベース接続失敗。' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>NEWS VIEW</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="statics/css/bootstrap.min.css">
  <script src="statics/js/jquery.min.js"></script>
  <script src="statics/js/popper.min.js"></script>
  <script src="statics/js/bootstrap.min.js"></script>
</head>
<body>

<div class="jumbotron text-center">
  <h1>Title of this website</h1>
  <p>Some description here!</p>
</div>

<div class="container">
  <div class="card">
    <div class="card-body">
      <div class="col">

        <div class="divider bg-dark"><hr></div>

        <h5>このニュースから銘柄を探す</h5>

        <div class="divider"><hr></div>

        <p> 
        <?php
            $news_id = 1;
            $sth = $pdo->prepare("SELECT * FROM news WHERE id = ".$news_id);
            $sth->execute();
            $row_news = $sth->fetch();
        ?>
            <?= htmlspecialchars($row_news['title']) ?>
            id = <?php echo $news_id; ?>
        </p>

        <p class="text-right">
          <span class="font-weight-bold"><a href="<?php echo $row['link']; ?>">このニュースを見る > </a></span>
        </p>

        <div class="divider"><hr></div>


        <p>
          <span class="font-weight-bold"> 関連ワード :</span>
          &nbsp &nbsp
          
          <?php
            $sth = $pdo->prepare("SELECT * FROM relation WHERE newsid = ".$news_id);
            $sth->execute();
            $keyword_id_arr = array();
            foreach($sth as $row) {
                $keyword_ids = array($row['keywordid1'],$row['keywordid2']);
                foreach($keyword_ids as $id_temp){
                    if (!in_array($id_temp, $keyword_id_arr)) {
                        array_push($keyword_id_arr, $id_temp);
                    }   
                }
            }
            sort($keyword_id_arr);
            //print_r($keyword_id_arr);
          ?>
          
          <?php
            
            $related_keywords = array();
            
            foreach($keyword_id_arr as $keyword_id){
                $sth = $pdo->prepare("SELECT * FROM keywords WHERE id = ".$keyword_id);
                $sth->execute();
                $row_keyword = $sth->fetch();
                
                // push related keywords here
                array_push($related_keywords, $row_keyword['keyword']);
            }
            
            foreach($related_keywords as $rkeyword){
          ?>
          
          <button type="button" class="btn btn-secondary">
          
          <?php
            echo $rkeyword;
          ?>
          
          </button>
          &nbsp
          
          <?php
            }
          ?>
 
        </p>

        <div class="divider bg-dark"><hr></div>

      </div>
    </div>
  </div>
</div>

</body>
</html>