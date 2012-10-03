<!DOCTYPE html>
<html>
<head>
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<meta charset="utf-8">

	<!-- libs -->
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
	<script type="text/javascript" src="/js/jquery.form.js"></script>

	<!-- style -->
	<link rel="stylesheet" type="text/css" href="/css/style.css">

	<!-- app -->
	<script type="text/javascript" src="/js/app.js?<?php echo rand()*999; ?>"></script>

</head>
<body>
	<div id="container">
		<?php echo $content; ?>
	</div>
</body>
</html>
