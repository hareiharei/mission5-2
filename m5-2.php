<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>mission5-1</title>
    </head>
    <body>
        <?php
        $dsn = 'mysql:dbname=**********;host=localhost';
        $user = '*********';
        $password = '**********';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        $sql = "CREATE TABLE IF NOT EXISTS bulletin_board"
        ."("
        ."id INT AUTO_INCREMENT PRIMARY KEY,"
        ."name VARCHAR(50),"
        ."comment TEXT,"
        ."password VARCHAR(300),"
        ."date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        .");";
        $pdo -> query($sql);

        $editname = "";
        $editcomment = "";
        $editpassword = "";
        $editnum = "";
        $message = "";
        
        if (isset($_POST["edit"])) {
            if (!empty($_POST["editnum"])) {
                $editnum = $_POST["editnum"];
                $password2edit = $_POST["password2edit"];
                $sql = 'SELECT * FROM bulletin_board WHERE id=:editnum';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':editnum', $editnum, PDO::PARAM_INT);
                $stmt -> execute();
                $result = $stmt -> fetch(PDO::FETCH_ASSOC);
                $password = $result['password'];
                
                if ($password2edit == $password) {
                    $editname = $result['name'];
                    $editcomment = $result['comment'];
                    $editpassword = $password;
                    $message = "投稿番号".$editnum."を編集しています。";
                } elseif (empty($password)) {
                    $message = "この投稿は編集できません。";
                } else {  // $password2edit != $password && !empty($password)
                    $message = "パスワードが間違っています。";
                }
            } else { $message = "値が入力されていません。"; }
        }
        ?>
        
        <form action="" method="POST">
            <input type="text" name="name" value="<?php if (!empty($editname)) {echo "$editname";} else {echo "名前";} ?>">
            <input type="text" name="comment" value="<?php if (!empty($editcomment)) {echo "$editcomment";} else {echo "コメント";} ?>">
            <input type="password" name="password" placeholder="パスワード">
            <input type="hidden" name="editting" value="<?php echo $editnum; ?>">
            <input type="submit" name="submit" value="送信">
        </form>
        <br>
        <form action="" method="POST">
            <input type="number" name="deletenum" placeholder="削除対象番号">
            <input type="password" name="password2delete" placeholder="パスワード">
            <input type="submit" name="delete" value="削除">
        </form>
        <br>
        <form action="" method="POST">
            <input type="number" name="editnum" placeholder="編集対象番号">
            <input type="password" name="password2edit" placeholder="パスワード">
            <input type="submit" name="edit" value="編集">
        </form>
        <br>
        
        <?php
        
        if (isset($_POST["submit"])) {
            $editting = $_POST["editting"];
            if (empty($editting)) {      
                //新規作成モード
                if (!empty($_POST["name"]) && !empty($_POST["comment"])) {
                    $name = $_POST["name"];
                    $comment = $_POST["comment"];
                    $password = $_POST["password"];
                    $date = date("Y/m/d H:i:s");
                    $sql = "INSERT INTO bulletin_board (name, comment, password, date) VALUES (:name, :comment, :password, :date)";
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt -> bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt -> execute();
                    $message = "投稿を新規作成しました。";
                } else { $message = "値が入力されていません"; }
            } else {    
                //編集モード
                if (!empty($_POST["name"]) && !empty($_POST["comment"])) {
                    $name = $_POST["name"];
                    $comment = $_POST["comment"];
                    $password = $_POST["password"];
                    $date = date("Y/m/d H:i:s");
                    $sql = 'UPDATE bulletin_board SET name=:name, comment=:comment, password=:password, date=:date WHERE id=:editting';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt -> bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt -> bindParam(':editting', $editting, PDO::PARAM_INT);
                    $stmt -> execute();
                    $message = "投稿番号".$editting."を編集しました。";
                } else { $message = "値が入力されていません。"; }
            }
            
        } elseif (isset($_POST["delete"])) {
            $editnum = 0;
            $editname = "";
            $editcomment = "";
            $editpassword = "";
            if (!empty($_POST["deletenum"])) {
                $deletenum = $_POST["deletenum"];
                $password2delete = $_POST["password2delete"];
                $sql = 'SELECT * FROM bulletin_board WHERE id=:deletenum';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':deletenum', $deletenum, PDO::PARAM_INT);
                $stmt -> execute();
                $result = $stmt -> fetch(PDO::FETCH_ASSOC);
                $password = $result['password'];
                if ($password2delete == $password) {
                    $sql = 'delete from bulletin_board where id=:deletenum';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':deletenum', $deletenum, PDO::PARAM_INT);
                    $stmt -> execute();
                    $message = "投稿番号".$deletenum."を削除しました。";
                } elseif (empty($password)) {
                    $message = "この投稿は削除できません。";
                } else {  //$password2delete != $password && !empty($password)
                    $message = "パスワードが間違っています。";
                }
            } else { $message = "値が入力されていません。"; }
        } 
        
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8')."<br>";
        $sql = 'SELECT * FROM bulletin_board';
        $stmt = $pdo -> query($sql);
        $results = $stmt -> fetchAll();
        foreach ($results as $row) {
            echo $row['id']." ";
            echo $row['name']." ";
            echo $row['comment']." ";
            echo $row['date']." ";
            if (empty($row['password'])) {
                echo '(パスワードなし)<br>';
            } else {
                echo '(パスワードあり)<br>';
            }
        }
        ?>

    </body>
</html>