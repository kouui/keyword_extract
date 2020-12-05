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
  
  <!-- bootstrap4 -->
  <link rel="stylesheet" href="statics/css/bootstrap.min.css">
  
  <script src="statics/js/jquery.min.js"></script>
  <script src="statics/js/popper.min.js"></script>
  <script src="statics/js/bootstrap.min.js"></script>
  
  <!-- d3.js -->
  <script src="https://d3js.org/d3.v5.js"></script>

  <!-- d3-cloud -->
  <script src="https://cdn.jsdelivr.net/gh/holtzy/D3-graph-gallery@master/LIB/d3.layout.cloud.js"></script>
  
</head>
<body>

<div class="jumbotron text-center">
  <h1>Title of this website</h1>
  <p>Some description here!</p>
</div>

<div class="col-md-12">
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
            
            $related_keywords_json = json_encode($related_keywords);
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
        
        <div id="wordcloud"></div>
        <p id="demo"></p>


        <div class="divider bg-dark"><hr></div>
        
        <h5>このニュースの関連銘柄</h5>
        
        <div class="divider"><hr></div>
        
        <table class="table table-striped">
            
            <tbody>
        
            <?php
            
            $sth = $pdo->prepare("SELECT * FROM news_company_table WHERE news_id = ".$news_id);
            $sth->execute();
            
            foreach($sth as $row){
                $sth2 = $pdo->prepare("SELECT * FROM companies WHERE id = ".$row['company_id']);
                $sth2->execute();
                $row_company = $sth2->fetch();
                $company_name = $row_company['name'];
            ?>
        
            <tr>
                <th scope="row"><?php echo $company_name ?></th>
            </tr>
            
            <?php
            }
            ?>
            </tbody>
            
        </table>
        
        
        
        <div class="divider bg-dark"><hr></div>
        
        <h5>関連ニュースから銘柄を探す</h5>
        
        <div class="divider"><hr></div>
        
        <div class="divider bg-dark"><hr></div>

      </div>
    </div>
  </div>
</div>

</body>
</html>

<script>


    var word_array = <?php echo $related_keywords_json; ?>;
    //document.getElementById("demo").innerHTML = word_array[0];

    // ワードリスト
    var myWords = [];
    for(var i = 0; i < word_array.length; i++){
        myWords.push( { word: word_array[i], size: "50", color: "#A4CABC" } );
    }


    // グラフの表示設定
    var margin = { top: 10, right: 10, bottom: 10, left: 10 },
        width = 450 - margin.left - margin.right,
        height = 300 - margin.top - margin.bottom;

    // svgオブジェクトの追加
    var svg = d3.select("#wordcloud").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform",
            "translate(" + margin.left + "," + margin.top + ")");

    // インスタンスの作成
    var layout = d3.layout.cloud()
        .size([width, height])
        .words(myWords.map(function (d) { return { text: d.word, size: d.size, color: d.color }; }))
        .padding(5)        //単語の距離
        .rotate(function () { return ~~(Math.random() * 2) * 90; })
        .fontSize(function (d) { return d.size; })      // フォントサイズ
        .on("end", draw);
    layout.start();

    // 'ayoutの出力を受け取り単語を描画
    function draw(words) {
        svg
            .append("g")
            .attr("transform", "translate(" + layout.size()[0] / 2 + "," + layout.size()[1] / 2 + ")")
            .selectAll("text")
            .data(words)
            .enter().append("text")
            .style("font-size", function (d) { return d.size; })
            .attr("fill", function (d) { return d.color;} )
            .attr("text-anchor", "middle")
            .style("font-family", "Impact")
            .attr("transform", function (d) {
                return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
            })
            .text(function (d) { return d.text; });
    }

</script>