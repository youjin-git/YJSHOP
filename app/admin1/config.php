<?php 
return array(
		'data_backup_path'=>ROOT_PATH.'public/database',
		'data_backup_part_size'=>20971520,
		'data_backup_compress'=>0,//数据库备份文件是否启用压缩 压缩备份文件需要PHP环境支持gzopen,gzwrite函数 0:不压缩1:启用压缩
		'data_backup_compress_level'=>9 //数据库备份文件的压缩级别，该配置在开启压缩时生效 1:普通4:一般9:最高
);
