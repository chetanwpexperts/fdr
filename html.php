<style>

</style>

<div class="container">
	<div class="row folderform">
		
		<div class="col-md-4">
			<h2>Add New Folder</h2>
			<form class="form-horizontal" method="post" id="newform">
			  <div class="form-group">
				<label>Folder Type</label>
				<select id="type">
					<option value="0">Select</option>
					<option value="main" selected>Main Folder</option>
					<option value="sub">Sub Folder</option>
				</select>
			  </div>
			  <div class="form-group ifsub" style="display:none;">
				<div>
					<?php echo do_shortcode('[folderoptions]');?>
				</div>
			  </div>
			  <div class="form-group">
				<label>Folder Number</label>
				<input type="text" name="random_number" id="random_number" value="<?php echo substr(sha1(mt_rand()),17,6);?>" id="" disabled />
			  </div>
			  <div class="form-group">
				<label>Folder Name</label>
				<input type="text" name="name" id="name" required />
			  </div>
			  <button type="submit" class="btn btn-default" name="submit" id="submit">Submit</button>
			</form>
		</div>
		<div class="col-md-8">
			<h2>Folder List</h2>
			<?php echo do_shortcode('[folderlist]');?>
		</div>
	</div>
	
	

</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script>
jQuery( document ).ready( function($)
{
	$("#type").on('change', function()
	{
		var val = $(this).val();
		if(val == "sub")
		{
			$(".ifsub").show();
		}else{
			$(".ifsub").hide();
		}
	});
	var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' );?>";
	jQuery("#submit").on( "click", function(e) 
	{
		e.preventDefault(); 
		
		var number = $("#random_number").val();
		var name = $("#name").val();
		var isparent = $("#isparent").val();
		
		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : ajaxurl,
			data : {action: "newfolder", number : number, name: name, parent: isparent},
			success: function(response) {
				if(response.type == "success") {
					$("#newform")[0].reset();
					location.reload();
				}
				else {
					alert("Your vote could not be added");
				}
			}
		});
		return false;
	});
	
	jQuery( document ).on( "click", '.subfold',function(e) 
	{
		e.preventDefault(); 
		var folder_id = $(this).attr('data-folderid');
		jQuery.ajax({
			type : "post",
			url : ajaxurl,
			data : {action: "getsubfolder", folder_id : folder_id},
			success: function(response) {
				$("#mainfolders").hide();
				$("#subfolders").hide();
				$("#subfolderslist").html(response);
			}
		});
		
		return false;
	});
});
</script>