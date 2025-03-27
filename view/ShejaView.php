<?php
    // include_once dirname(__DIR__) . "/controller/ShejaController.php";
    // ShejaController::shelveNIO();
    ShejaController::pickupSessionOptions();
?>
<html>
<head>
  <?php
  include_once dirname(__DIR__) . "/includes/common.head.php";
  ?>
  <link rel="stylesheet" href="css/shejaView.css" />
    <title>shes bya kun khyab yig rigs khag gnyis mnyam du bsgrigs pa</title>

    <script>
        function provideRows(str)
        {
            var tbody = document.getElementById("appendix");
            if (str.length === 0) {
                tbody.innerHTML = "";
            } else {
                var xml = new XMLHttpRequest();
                xml.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        tbody.innerHTML = this.responseText;
                        tbody.parentElement.parentElement.classList.remove("d-none");
                    }
                };
                xml.open("GET", "view/appendix.php?a=" + str, false);
                xml.send();
            }
        }
    </script>

</head>
<body>
<div class="jumbotron jumbotron-fluid">
    <div class="container">
        <h1 class="display-4">shes bya kun khyab yig rigs khag gnyis mnyam du bsgrigs pa</h1>
        <hr class="my-2" />
        <?php
            $epithets = array("concurrent", "linked", "synchronized", "concordant", "parallel");
            $s = rand(1, count($epithets) - 1);
            $t = rand(0, count($epithets) - 1);
            while( $s === $t) {
                $t = rand(0, count($epithets) - 1);
            }
            echo "<p class=\"lead\">" . $epithets[$s] . " search across multiple " . $epithets[$t] . " texts</p>" . PHP_EOL;
        ?>
    </div>
</div>

<div class="while-progress d-none">
  <i class="fa fa-2x fa-spinner fa-spin"></i>
    <label>Message...</label>
</div>

<div class="container">

    <form action="" method="GET" target="_blank">
        <?php
            $ul = null;
            echo "<div class='book-list'>" . PHP_EOL;
            foreach (ShejaController::$bookShelf->getBooks() as $book)
            {
                if( !($book->getUL() == $ul) )
                {
                    // completing the previous <ul> if it was started:
                    if( !is_null($ul) ) echo "</div>" . PHP_EOL . "</ul>" . PHP_EOL;

                    // starting new <ul>:
                    $ul = $book->getUL();
                    echo "<ul class='mb-2'>" . PHP_EOL;

                    // the actual checked status of this input is set in JS
                    echo "<input class='form-check-input' type='checkbox'/>" . PHP_EOL;
                    echo "<strong><a href='#' class='text-dark'>" . $ul . "</a></strong>" . PHP_EOL;
                    echo "<div>" . PHP_EOL;
                }
                echo "<div class='form-check'>" . PHP_EOL;
                echo "<input class='form-check-input' type='checkbox' id='book" . $book->getId() . "' value='" . $book->getId() . "' name='v'";
                if ($book->isChecked()) echo (" checked");
                echo "/>" . PHP_EOL;
                echo ("<label class='form-check-label' for='book" . $book->getId() . "'>");
                $editions = $book->getEditions();
                for ($i = 0; $i < count($editions); $i++)
                {
                    if ($i !== 0) echo (" / ");
                    echo ($editions[$i]->getTitle());
                }
                echo "</label>" . PHP_EOL;
                echo "</div>" . PHP_EOL;
            }
            // completing the last <ul> if it was started:
            if($ul != null) echo "</div>" . PHP_EOL . "</ul>" . PHP_EOL;

            echo "</div>" . PHP_EOL;
        ?>
        <div class="row mt-3">
            <div class="col"></div>
            <div class="col alert alert-dark">
                <div class="form-check form-check-inline form-control-sm">
                    <input class="form-check-input" type="checkbox" id="matchCase" name="case" value="1">
                    <label class="form-check-label" for="matchCase">Match Case</label>
                </div>
                <div class="form-check form-check-inline form-control-sm">
                    <input class="form-check-input" type="checkbox" id="wholeWord" name="word" value="2">
                    <label class="form-check-label" for="wholeWord">Words</label>
                </div>
                <div class="form-check form-check-inline form-control-sm">
                    <input class="form-check-input" type="checkbox" id="wylie" name="wylie" value="4" disabled>
                    <label class="form-check-label" for="wylie">Wylie</label>
                </div>
                <div class="form-check form-check-inline form-control-sm">
                    <input class="form-check-input" type="checkbox" id="regEx" name="regex" value="8">
                    <label class="form-check-label" for="regEx">RegEx</label>
                </div>
            </div>

            <div class="w-100"></div>

            <div class="col-2"></div>
            <div class="col alert alert-dark">
                <div class="input-group">
                    <input type="search" class="form-control rounded" placeholder="Search" aria-label="Search"
                           aria-describedby="search-addon" name="q"/>
                    <button type="submit" class="btn btn-outline-dark ml-2"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
        <input type="hidden" name="p" value="delivery">
    </form>

    <nav class="navbar navbar-expand-sm navbar-light bg-light">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="">Glossary</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="">Names</a>
            </li>
            <li class="nav-item disabled">
                <a class="nav-link disabled" href="">Indexes</a>
            </li>
        </ul>
    </nav>

  <div class="table-responsive h-50 d-none">
    <table class="table table-sm table-striped">
            <thead class="thead-dark sticky-top" style="z-index:1;">
                <tr>
                    <th class="d-none">id</th>
                    <th>Tibetan</th>
                    <th>Sanskrit</th>
                    <th>English</th>
                    <th class="d-none">book</th>
                    <th class="d-none">comment</th>
                </tr>
            </thead>
            <tbody id="appendix">
                <!--rows to insert-->
            </tbody>
        </table>
  </div>
</div>

<?php
include_once dirname(__DIR__) . "/includes/common.shoes.php";
?>
<script src="js/shejaView.js"></script>

</body>
</html>
