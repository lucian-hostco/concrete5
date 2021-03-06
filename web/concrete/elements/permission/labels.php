<? defined('C5_EXECUTE') or die("Access Denied."); ?>
<?
if (!isset($pa)) {
	$pa = $pk->getPermissionAccessObject();
}
$assignments = array();
$paID = 0;
if (is_object($pa)) {
	$paID = $pa->getPermissionAccessID();
	$assignments = $pa->getAccessListItems(PermissionKey::ACCESS_TYPE_ALL);
}

?>
<div class="ccm-permission-access-line">
<?
$str = '';

if (count($assignments) > 0) {
	for ($i = 0; $i < count($assignments); $i++) { 
		$class = '';
		$as = $assignments[$i];
		$entity = $as->getAccessEntityObject();
		$pd = $as->getPermissionDurationObject();
		$pdTitle = '';
		
		if ($as->getAccessType() == PermissionKey::ACCESS_TYPE_EXCLUDE) {
			if (is_object($pd)) {
				$class = 'label-warning';
				$pdTitle = 'title="' . $pd->getTextRepresentation() . '"';
			} else {
				$class = 'label-important';
			}
		} else { 
			if (is_object($pd)) {
				$class = 'label-info';
				$pdTitle = 'title="' . $pd->getTextRepresentation() . '"';
			}
		}
		$str .= '<span class="label ' . $class . '" ' . $pdTitle . '>' . $entity->getAccessEntityLabel() . '</span> ';
	}
}

?>
<? if (!$str) { ?>
	<span style="color: #ccc"><?=t('None')?>
<? } else { ?>
	<?=$str?>
<? } ?>

<input type="hidden" name="pkID[<?=$pk->getPermissionKeyID()?>]" value="<?=$paID?>" />
</div>

<script type="text/javascript">
$(function() {
	$('.ccm-permission-access-line span[title]').tooltip();
	$('.ccm-permission-grid-cell .ccm-permission-access-line').draggable({
		helper: 'clone'	
	});
	$('.ccm-permission-grid-cell').droppable({
		accept: '.ccm-permission-access-line',
		hoverClass: 'ccm-permissions-grid-cell-active',
		drop: function(ev, ui) {
			var paID = $(ui.draggable).find('input').val();
			var pkID = $(this).attr('id').substring(25);

			$(ui.draggable).clone().appendTo($('#ccm-permission-grid-cell-' + pkID).html(''));
			$('#ccm-permission-grid-name-' + pkID + ' a').attr('data-paID', paID);	
			$('#ccm-permission-grid-cell-' + pkID + ' input[type=hidden]').attr('name', 'pkID[' + pkID + ']');	
			$('#ccm-permission-grid-cell-' + pkID + ' div.ccm-permission-access-line').draggable({
				helper: 'clone'
			});			
		}
		
	});
});
</script>