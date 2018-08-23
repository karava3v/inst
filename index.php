<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Instagram data viewer</title>
</head>
<body>
    <form method="post" action="upload.php" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <label id="file">Max allowed 100 MB</label>
        <button type="submit">Send</button>
    </form>
</body>
</html>