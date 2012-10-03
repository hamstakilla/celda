$(document).ready(function() {

	/* 	just a prototype
		needs refactoring */

	var MAX_COLOR = 16777215;
	var W = $(document).width();
	var H = $(document).height();

	var celda = $('.celda');
	var mutex = [];
	var mouse = { x: 0, y: 0 };
	var mouseClickedOn = { x: 0, y: 0 };
	var nodes = [];
	var elements = [];
	var tillUpdate = 0;
	var maxId = rootId;

	var pivotPoint = {	x: ($(document).width() / 2) - 70,
						y: 50	};

	function mouseDiff() {
		return { x: mouse.x - mouseClickedOn.x, y: mouse.y - mouseClickedOn.y };
	}

	tick();
	function tick() {
		creationTick();
		setTimeout(tick, 50);
	}
	update();
	function update() {
		if (tillUpdate == 0) {
			$.getJSON("/reader/getNodes/"+rootId+"/"+maxId+"?rand="+Math.random(), function(data) {
				nodes = data;
				buildTree();
			});
			tillUpdate = 3;
		} else {
			tillUpdate--;
		}
		drawStats();
		setTimeout(update, 1000);
	}

	function drawStats() {
		$('.stat').html('Refresh in: '+tillUpdate+' sec.');
	}

	$(document).mousemove(function(e) {
		mouse = { x: e.pageX, y: e.pageY };
	});

	$(document).mouseup(function(e) {
		if (!creationClick())
			return;
		mouseClickedOn = { x: e.pageX, y: e.pageY };	
	});

	$(document).bind('contextmenu', function(e) {
		return creationRightClick();
	});

	// create nodes

	function doAdd(id) {
		for (var i = 0; i < mutex.length; i++) {
			if (mutex[i]==id)
				return false;
		}
		mutex.push(id);
		return true;
	}

	function getNode(id) {
		for (var i = 0; i < elements.length; i++) {
			if (elements[i].id==id)
				return elements[i];
		}
		return false;		
	}

	function buildTree() {
		createNodes(pivotPoint, MAX_COLOR, nodes[0]);
	}

	var simpleNodeHtmlX = $('#simple-node').html();
	$('#simple-node').remove();
	var creationNodeHtmlX = $('#creation-node').html();
	$('#creation-node').remove();

	function simpleNodeHtml(data) {
		if (data.title == null)
			data.title = '';
		if (data.content == null)
			data.content = '';
		var html = simpleNodeHtmlX;
		html = html.replace('%data.id', data.id);
		html = html.replace('%data.thread_id', data.thread_id);
		html = html.replace('%data.title', data.title);
		var thumbnail = data.image != null ? '<a href="/up/'+data.image+'" target="pic" ><img src="/up/'+data.image.replace('.','_thumb.')+'" align="left" class="thumbnail" /></a>' : '';
		html = html.replace('%data.content', thumbnail+data.content);
		html = html.replace('null', '');
		return html;
	}

	function createNodes(pivot, initialColor, data) {
		var childrens = [];
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].parent_id == data.id) {
				childrens.push(nodes[i]);
				continue;
			}
		}
		var node = $(simpleNodeHtml(data));
		if (data.id > maxId)
			maxId = data.id;
		//var node = $('<div id="node" uid="'+data.id+'" tid="'+data.thread_id+'">'+data.title+'<div id="bottom"><div id="controls"><a id="answer" href="#">+</a></div></div></div>');
		node.find('#answer').click(newNode);
		var newPivot = {	x: pivot.x + parseInt(data.x),
							y: pivot.y + parseInt(data.y)	};		
		node.css({
			left: newPivot.x+'px',
			top: newPivot.y+'px'
		});
		node.id = data.id;
		node.color = initialColor - 0xf;
		node.find('.thumbnail').click(function() {
			var src = $(this).attr('src');
			var inThumb = src.indexOf('_thumb.')>0;
			$(this).attr('src', inThumb ? src.replace('_thumb.', '.') : src.replace('.', '_thumb.'));
			inThumb ? node.addClass('extended') : node.removeClass('extended');
			return false;
		});
		node.mouseout(function() {
			return;
			var src = node.find('.thumbnail').attr('src');
			if (src.indexOf('_thumb.')<0)
				node.find('.thumbnail').attr('src', src.replace('.', '_thumb.'));
		});
		if (node.color < 0)
			node.color = MAX_COLOR;
		node.css('border-color', '#'+intToColor(node.color));
		if (doAdd('node'+data.id)) {
			celda.append(node);
			//node.find('#content').html(node.id+' '+(parseInt(node.css('left'))-882)+' : '+(parseInt(node.css('top'))-50));
			elements.push(node);
		}
		for (var i = 0; i < childrens.length; i++) {
			var childNode = createNodes(newPivot, node.color, childrens[i]);
			if (doAdd('line'+childNode.id)) {
				var line = createLine(childNode.id, getNode(data.id), childNode);
				line.css('background-color', '#'+intToColor(node.color));
				celda.append(line);
			}
		}
		return node;
	}

	// node creation 

	var moveCreation = false;
	var parentNode = undefined;
	var creationLine = createLineEx(-1);
	var creationNode = $(creationNodeHtmlX);
	//creationNode.find('#save').click(saveNode);
	creationNode.find('#save').click(function() {
		creationNode.find('form').submit();
		return false;
	});
	creationNode.find('form').submit(function() {
		saveNode();
	});
	creationNode.find('#cancel').click(cancelNode);
	//creationNode.find('form').submit(saveNode);
	creationNode.find('#cnode-file-click').click(function () {
		creationNode.find('#cnode-file').click();
		return false;
	});
	creationNode.find('#cnode-file').change(function() {
		var name = $(this).val();
		name = name.substr(name.lastIndexOf('.')+1);
		creationNode.find('#cnode-file-click').html(name);
		return false;
	});
	var resubmit = true;
	creationNode.find("#cnode-form").ajaxForm({
		url: "/reader/newNode",
		type: "post",
		resetForm: true,
		clearForm: true,
		success: function(responseText, statusText, xhr, $form) {
			if (responseText == 'success')
				cancelNode();
			if (responseText == 'relocate') {
				creationNode.addClass('denied');
				moveCreation = true;
				return false;
			}
		},
		beforeSubmit: function(arr, $form, options) { 
		}
	});
	celda.append(creationLine);
	creationLine.hide();
	celda.append(creationNode);
	creationNode.hide();

	function creationTick() {
		if (typeof parentNode === 'undefined')
			return;
		if (!moveCreation)
			return;
		var mDiff = mouseDiff();
		var diff =  mDiff;
		var pivot = mouseClickedOn;

		if (isInt(creationNode.css('left')))
			if (isInt(creationNode.css('top')))
				if (isInt(parentNode.css('left')))
					if (isInt(parentNode.css('top'))) {
						diff = {
							x: parseInt(creationNode.css('left')) - parseInt(parentNode.css('left')) + 100,
							y: parseInt(creationNode.css('top')) - parseInt(parentNode.css('top')) + 100
						};
						pivot = {
							x: parseInt(parentNode.css('left')),
							y: parseInt(parentNode.css('top'))
						}
					}

		var cantMove = false;
		if (diff.y <= 100)
			cantMove = true;
		if (diff.y > 350)
			cantMove = true;
		if (diff.x > 0 ? diff.x > 500 : diff.x < -500)
			cantMove = true;
		for (var i = 0; i < elements.length; i++) {
			var element = elements[i];
			if (cantMove)
				break;
			if (distanceElements(element, creationNode) < 150)
				cantMove = true;
		};
		cantMove ? creationNode.addClass('denied') : creationNode.removeClass('denied');
		creationNode.css({
			left: (mouseClickedOn.x + mDiff.x) - 100 + 'px',
			top: (mouseClickedOn.y + mDiff.y) - 50 + 'px'
		});
		connectLine(creationLine, parentNode, creationNode);
	}

	function creationClick() {
		if (!moveCreation)
			return true;
		if (typeof parentNode === 'undefined')
			return true;
		if (creationNode.is('.denied'))
			return false;
		moveCreation = false;
	}

	function creationRightClick() {		
		if (typeof parentNode === 'undefined')
			return true;
		cancelNode();
		moveCreation = false;
		return false;
	}

	function newNode() {
		if (creationNode != null)
			if (moveCreation && creationNode.css('display') != 'none')
				return false;
		creationLine.show();
		creationNode.show();
		parentNode = $(this).parent().parent().parent();
		moveCreation = true;
		return false;
	}

	function saveNode() {
		var x = parseInt(creationNode.css('left')) - parseInt(parentNode.css('left'));
		var y = parseInt(creationNode.css('top')) - parseInt(parentNode.css('top'));

		creationNode.find("#cnode-thread-id").val(parentNode.attr('tid'));
		creationNode.find("#cnode-parent-id").val(parentNode.attr('uid'));
		creationNode.find("#cnode-x").val(x);
		creationNode.find("#cnode-y").val(y);
	}

	function cancelNode() {
		creationNode.hide();
		creationLine.hide();
		creationNode.find("#cnode-content").val('');
		creationNode.find("#cnode-file").val('');
		creationNode.find("#cnode-file-click").html('upload image');
		parentNode = undefined;
		moveCreation = false;
		return false;
	}

	// misc

	function intToColor(val) {
		var result = val.toString(16);
		if (result.length < 6)
			for (var i = result.length; i < 6; i++)
				result = '0'+result;
		return result;
	}

	function isInt(input) {
		return (typeof parseInt(input) === 'number') && (!isNaN(parseInt(input)));
	}

	function difference(a, b) {
		return Math.max(a, b) - Math.min(a, b);
	}

	function distance(x1, y1, x2, y2) {
		return Math.sqrt(((x2-x1) * (x2-x1)) + ((y2-y1) * (y2-y1)));
	}

	function distanceElements(el1, el2) {
		var off1 = getOffset(el1);
	    var off2 = getOffset(el2);
	    // bottom right
	    var x1 = off1.left;
	    var y1 = off1.top;
	    // top right
	    var x2 = off2.left;
	    var y2 = off2.top;
	    return distance(x1, y1, x2, y2);
	}

	function connectLine(line, div1, div2) {
		var off1 = getOffset(div1);
	    var off2 = getOffset(div2);
	    // bottom right
	    var x1 = off1.left + off1.width;
	    var y1 = off1.top + off1.height;
	    // top right
	    var x2 = off2.left + off2.width;
	    var y2 = off2.top;
	    // distance
	    var length = distance(x1, y1, x2, y2);
	    // center
	    var cx = ((x1 + x2) / 2) - (length / 2);
	    var cy = ((y1 + y2) / 2);
	    // angle
	    var angle = Math.atan2((y1-y2),(x1-x2))*(180/Math.PI);
	    // make hr
	    line.css({
	    	'left': cx+'px',
	    	'top': cy+'px',
	    	'width': length+'px',
	    	'-moz-transform': 'rotate(' + angle + 'deg)',
	    	'-webkit-transform': 'rotate(' + angle + 'deg)',
	    	'-o-transform': 'rotate(' + angle + 'deg)',
	    	'-ms-transform': 'rotate(' + angle + 'deg)',
	    	'transform': 'rotate(' + angle + 'deg)',
	    });
	}

	function createLineEx(id) {
	    var line = $('<div id="line" uid="' + id + '" />');
	    return line;
	}

	function createLine(id, div1, div2) {
		var line = createLineEx(id);
	    connectLine(line, div1, div2);
	    return line;
	}

	function getOffset(el) {
	    _x = parseInt(el.css('left'));
	    _y = parseInt(el.css('top'));
	    _w = el.width() / 2;
	    _h = el.height() / 2;
	    return { top: _y, left: _x, width: _w, height: _h };
	}

});
