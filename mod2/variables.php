<?php
// mod2/ reminder to have one page with buttons for all parts 

$part = $_POST['part'] ?? $_GET['part'] ?? '';
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Home Page</title>
  <style>
    body{ font-family: -apple-system, system-ui, Segoe UI, Roboto, Arial; margin: 20px; }
    h1{ font-size: 20px; margin: 0 0 8px; }
    h2{ font-size: 26px; margin: 18px 0 12px; }
    .links h3{ font-size: 24px; margin: 12px 0; }
    .links form{ display: inline-block; margin-right: 10px; margin-bottom: 10px; }
    .btn{ padding: 6px 10px; border: 1px solid #888; background: #eee; cursor: pointer; border-radius: 4px; }
    .card{ margin-top: 14px; padding: 14px; border: 1px solid #ddd; border-radius: 6px; background: #fafafa; }
    table{ border-collapse: collapse; }
    td,th{ border:1px solid #ccc; padding:6px 10px; }
    .muted{ color:#666; }
  </style>
</head>
<body>
  <h1>Main Page</h1>

  <div class="links">
    <h3>Links</h3>

    <!-- Buttons to choose a part -->
    <form method="get"><button class="btn" name="part" value="1">Hello PHP</button></form>
    <form method="get"><button class="btn" name="part" value="2">Variables &amp; Strings</button></form>
    <form method="get"><button class="btn" name="part" value="3">Calculator</button></form>
    <form method="get"><button class="btn" name="part" value="4">Grades</button></form>
    <form method="get"><button class="btn" name="part" value="5">Loops</button></form>
    <form method="get"><button class="btn" name="part" value="6">Form Handling</button></form>
  </div>

  <div class="card">
  <?php
    switch($part){
      case '1':
        // Part 1: Hello PHP
        $yourName = "Ethan Van Scoy"; 
        echo "<h2>Part 1: Hello PHP</h2>";
        echo "<p><strong>Ethan Van Scoy</strong></p>";
        echo "<p><strong>Hello, ".h($yourName)."!</strong></p>";
        break;

      case '2':
        // Part 2: Variables & Strings
        $name = "Ethan";
        $age  = 23;
        echo "<h2>Part 2: Variables &amp; Strings</h2>";
        echo "<p>My name is ".h($name)." and I am ".h((string)$age)." years old.</p>";
        break;

      case '3':
        // Part 3: Calculator 
        $num1 = 12; $num2 = 4;
        $sum  = $num1 + $num2;
        $diff = $num1 - $num2;
        $prod = $num1 * $num2;
        $quot = ($num2!=0) ? $num1 / $num2 : '∞ (division by zero)';
        echo "<h2>Part 3: Simple Calculator</h2>";
        echo "<p>The sum of $num1 and $num2 is <strong>$sum</strong></p>";
        echo "<p>The difference of $num1 and $num2 is <strong>$diff</strong></p>";
        echo "<p>The product of $num1 and $num2 is <strong>$prod</strong></p>";
        echo "<p>The quotient of $num1 and $num2 is <strong>$quot</strong></p>";
        break;

      case '4':
        // Part 4: Conditional Statements (Grades)
        $grade = 87; // 0–100
        if($grade>=90 && $grade<=100){ $letter='A'; }
        elseif($grade>=80){ $letter='B'; }
        elseif($grade>=70){ $letter='C'; }
        else{ $letter='Fail'; }
        echo "<h2>Part 4: Conditional Statements</h2>";
        echo "<p>Numeric grade: <strong>$grade</strong></p>";
        echo "<p>Letter grade: <strong>$letter</strong></p>";
        break;

      case '5':
        // Part 5: Loops
        echo "<h2>Part 5: Loops</h2>";
        echo "<h4>Numbers 1–10</h4>";
        $nums=[]; for($i=1;$i<=10;$i++) $nums[]=$i;
        echo "<p>".implode(", ",$nums)."</p>";
        echo "<h4>Multiplication Table of 5</h4>";
        echo "<table><tr><th>Expression</th><th>Result</th></tr>";
        for($i=1;$i<=10;$i++){
          echo "<tr><td>5 × $i</td><td>".(5*$i)."</td></tr>";
        }
        echo "</table>";
        break;

      case '6':
        // Part 6: Form Handling
        $submitted = isset($_POST['__form6']);
        $name6 = trim($_POST['name6'] ?? '');
        $age6  = trim($_POST['age6'] ?? '');
        echo "<h2>Part 6: Form Handling</h2>";
        if($submitted && $name6!=='' && $age6!==''){
          echo "<p><strong>Hello, ".h($name6)."!</strong> You are ".h($age6)." years old.</p>";
          echo '<p class="muted"><a href="?part=6">Fill the form again</a></p>';
        } else {
          if($submitted) echo '<p style="color:#b00"><strong>Please enter both name and age.</strong></p>';
          echo '<form method="post">';
          echo '  <input type="hidden" name="part" value="6">';
          echo '  <input type="hidden" name="__form6" value="1">';
          echo '  <p><label>Name: <input type="text" name="name6" value="'.h($name6).'"></label></p>';
          echo '  <p><label>Age: <input type="number" name="age6" value="'.h($age6).'"></label></p>';
          echo '  <p><button class="btn" type="submit">Submit</button></p>';
          echo '</form>';
        }
        break;

      default:
        echo '<p class="muted">Click a button above to show a part.</p>';
    }
  ?>
  </div>
</body>
</html>
