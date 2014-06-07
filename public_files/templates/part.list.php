<?php foreach($_['files'] as $file):
    $simple_file_size = simple_file_size($file['size']);
    $simple_size_color = intval(200-$file['size']/(1024*1024)*2); // the bigger the file, the darker the shade of grey; megabytes*2
    if($simple_size_color<0) $simple_size_color = 0;
    ?>
    <tr data-file="<?php echo $file['file_name'];?>" data-mime="<?php echo $file['mime']?>" data-size='<?php echo $file['size'];?>'>
        <td class="filename svg" style="background-image:url(<?php  echo mimetype_icon($file['mime']); ?>)">
            <?php if(!isset($_['readonly']) || !$_['readonly']) { ?><input type="checkbox" /><?php } ?>
            <a class="name" href="<?php  echo $_['downloadURL'].urlencode($file['path']).urlencode($file['file_name']); ?>" title="<?php echo htmlspecialchars($file['path'].$file['file_name']);?>">
					<span class="nametext">
                        <?php echo htmlspecialchars($file['path'].$file['file_name']);?><span class='extention'><?php echo $file['extention'];?></span>
					</span>
            </a>
        </td>
        <td class="filesize" title="<?php $file['size'] ?>"><?php echo human_file_size($file['size']); ?></td>
        <td class="date"><span class="modified" title="<?php echo $file['date']; ?>"><?php echo $file['mtime']; ?></span></td>
    </tr>
<?php endforeach; ?>
