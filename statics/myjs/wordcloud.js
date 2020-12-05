

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
