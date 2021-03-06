		<?php foreach($_['files'] as $file):
			$simple_file_size = simple_file_size($file['size']);
			$simple_size_color = intval(200-$file['size']/(1024*1024)*2); // the bigger the file, the darker the shade of grey; megabytes*2
			if($simple_size_color<0) $simple_size_color = 0;
			$relative_modified_date = relative_modified_date($file['mtime']);
			$relative_date_color = round((time()-$file['mtime'])/60/60/24*14); // the older the file, the brighter the shade of grey; days*14
			if($relative_date_color>200) $relative_date_color = 200; ?>
			<tr data-file="<?php echo $file['name'];?>" data-type="<?php echo ($file['type'] == 'dir')?'dir':'file'?>" data-mime="<?php echo $file['mime']?>" data-size='<?php echo $file['size'];?>'>
				<td class="filename svg" style="background-image:url(<?php if($file['type'] == 'dir') echo mimetype_icon('dir'); else echo mimetype_icon($file['mime']); ?>)">
					<?php if(!isset($_['readonly']) || !$_['readonly']) { ?><input type="checkbox" /><?php } ?>
					<a class="name" href="<?php if($file['type'] == 'dir') echo $_['baseURL'].$file['directory'].'/'.$file['name']; else echo $_['downloadURL'].urlencode($file['directory']).'/'.urlencode($file['name']); ?>"
                       title="<?php echo $file['type'] == 'dir'? htmlspecialchars($file['name']): htmlspecialchars($file['basename'])?>">

					<span class="nametext">
						<?php if($file['type'] == 'dir'):?>
							<?php echo htmlspecialchars($file['name']);?>
						<?php else:?>
							<?php echo htmlspecialchars($file['basename']);?><span class='extention'><?php echo $file['extention'];?></span>
						<?php endif;?>
					</span>
					</a>
				</td>
				<td class="filesize" title="<?php echo $file['size']; ?>" style="color:rgb(<?php echo $simple_size_color.','.$simple_size_color.','.$simple_size_color ?>)"><?php echo human_file_size($file['size']); ?></td>
				<td class="date"><span class="modified" title="<?php echo $file['date']; ?>" style="color:rgb(<?php echo $relative_date_color.','.$relative_date_color.','.$relative_date_color ?>)"><?php echo $relative_modified_date; ?></span></td>
			</tr>
		<?php endforeach; ?>
