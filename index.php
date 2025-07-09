<?php

// タスクファイルへのパスを定義
$taskFile = 'tasks.txt';

// --- (1) タスクの追加処理 ---
// HTTP POSTリクエストがあり、フォームの 'action' が 'add' の場合
if (isset($_POST['action']) && $_POST['action'] === 'add') {
  // フォームから送信されたタスクのテキストを取得し、前後の空白を除去
  $newTask = trim($_POST['task']);

  // タスクが空文字列でなければファイルに書き込む
  if (!empty($newTask)) {
    // 新しいタスクをファイルに追記する (末尾に改行を追加)
    // FILE_APPEND: ファイルの最後に追記するモード
    // LOCK_EX: ファイルを排他的にロックし、他のプロセスからの同時書き込みを防ぐ
    file_put_contents($taskFile, $newTask . PHP_EOL, FILE_APPEND | LOCK_EX);
  }

  // 処理後にページをリダイレクトし、ブラウザの更新による重複送信を防ぐ (PRGパターン)
  header('Location: index.php');
  exit(); // リダイレクト後はスクリプトの実行を停止
}

// --- (2) タスクの削除処理 ---
// HTTP GETリクエストがあり、'action' が 'delete' かつ 'id' がURLパラメータとして存在する場合
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
  // 削除対象のタスクID（配列のインデックス）を取得し、整数型に変換
  $taskIdToDelete = (int)$_GET['id'];

  // 現在の全てのタスクをファイルから読み込む
  // FILE_IGNORE_NEW_LINES: 各行末の改行文字を削除して配列要素にする
  // FILE_SKIP_EMPTY_LINES: 空行を無視する
  $tasks = file($taskFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  // 指定されたIDのタスクが配列内に存在するか確認
  if (isset($tasks[$taskIdToDelete])) {
    // 配列から指定されたタスクを削除
    unset($tasks[$taskIdToDelete]);

    // 変更されたタスクリストを再度ファイルに書き込む (ファイル全体を上書き)
    // implode(PHP_EOL, $tasks): 配列の各要素をPHP_EOLで結合して1つの文字列にする
    file_put_contents($taskFile, implode(PHP_EOL, $tasks) . PHP_EOL, LOCK_EX);
  }

  // 処理後にページをリダイレクト
  header('Location: index.php');
  exit();
}

// --- (3) タスクの読み込み（表示用） ---
// ページが読み込まれるたびに、最新のタスクリストをファイルから読み込む
$tasks = file($taskFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHPだけのTODOリスト</title>
  <style>
    /* ここにCSSスタイルを記述 */
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background-color: #f4f4f4;
    }

    .container {
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      max-width: 600px;
      margin: 30px auto;
    }

    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
    }

    form {
      display: flex;
      margin-bottom: 20px;
    }

    input[type="text"] {
      flex-grow: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px 0 0 4px;
      font-size: 16px;
    }

    button {
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 0 4px 4px 0;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background-color: #0056b3;
    }

    ul {
      list-style: none;
      padding: 0;
    }

    li {
      background-color: #e9ecef;
      padding: 12px 15px;
      margin-bottom: 10px;
      border-radius: 4px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    li:nth-child(even) {
      background-color: #dee2e6;
    }

    .delete-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
    }

    .delete-btn:hover {
      background-color: #c82333;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>シンプルなTODOリスト</h1>

    <form action="index.php" method="POST">
      <input type="text" name="task" placeholder="新しいタスクを入力" required>
      <button type="submit" name="action" value="add">追加</button>
    </form>

    <?php if (empty($tasks)): // タスクが一つもない場合 
    ?>
      <p style="text-align: center; color: #666;">まだタスクはありません。</p>
    <?php else: // タスクがある場合 
    ?>
      <ul>
        <?php foreach ($tasks as $id => $task): // 各タスクをループで表示 
        ?>
          <li>
            <span><?php echo htmlspecialchars($task); ?></span>

            <a href="index.php?action=delete&id=<?php echo $id; ?>" class="delete-btn">削除</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</body>

</html>