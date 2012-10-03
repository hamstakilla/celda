<script type="text/javascript">
	var rootId = <?php echo $message['id']; ?>;
</script>

<div id="creation-node">
	<div id="node" class="creation" uid="-1" tid="-1">
		<form id="cnode-form">
			<div id="leftside">
				<div id="cnode-file-click">upload image</div>
				<input id="cnode-file" type="file" name="message[file]" />
			</div>
			<div id="rightside">
				<input id="cnode-parent-id" type="hidden" name="message[parent_id]" value="" />
				<input id="cnode-thread-id" type="hidden" name="message[thread_id]" value="" />
				<input id="cnode-x" type="hidden" name="message[x]" value="" />
				<input id="cnode-y" type="hidden" name="message[y]" value="" />
				<input id="cnode-title" name="message[title]" value="" />
				<textarea id="cnode-content" name="message[content]"></textarea>
				<input id="cnode-submit" type="submit" />
			</div>
			<div id="bottom">
				<div id="controls">
					<a id="save" href="#">save</a>
					<a id="cancel" href="#">cancel</a>
				</div>
			</div>
		</form>
	</div>
</div>
<div id="simple-node">
	<div id="node" uid="%data.id" tid="%data.thread_id">
		<div id="title">%data.title</div>
		<div id="content">%data.content</div>
		<div id="bottom">
			<div id="controls">
				<a id="answer" href="#">+</a>
			</div>
		</div>
	</div>
</div>

<div class="celda">

</div>

<div class="stat">
	Refresh in: 5 sec
</div>