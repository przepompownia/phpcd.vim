call extend(g:php_builtin_functions, {
\ 'mysql_affected_rows(': '[ resource $link_identifier = NULL] | int',
\ 'mysql_client_encoding(': '[ resource $link_identifier = NULL] | string',
\ 'mysql_close(': '[ resource $link_identifier = NULL] | bool',
\ 'mysql_connect(': '[ string $server = ini_get("mysql.default_host") [, string $username = ini_get("mysql.default_user") [, string $password = ini_get("mysql.default_password") [, bool $new_link = false [, int $client_flags = 0]]]]] | resource',
\ 'mysql_create_db(': 'string $database_name [, resource $link_identifier = NULL] | bool',
\ 'mysql_data_seek(': 'resource $result, int $row_number | bool',
\ 'mysql_db_name(': 'resource $result, int $row [, mixed $field = NULL] | string',
\ 'mysql_db_query(': 'string $database, string $query [, resource $link_identifier = NULL] | resource',
\ 'mysql_drop_db(': 'string $database_name [, resource $link_identifier = NULL] | bool',
\ 'mysql_errno(': '[ resource $link_identifier = NULL] | int',
\ 'mysql_error(': '[ resource $link_identifier = NULL] | string',
\ 'mysql_escape_string(': 'string $unescaped_string | string',
\ 'mysql_fetch_array(': 'resource $result [, int $result_type = MYSQL_BOTH] | array',
\ 'mysql_fetch_assoc(': 'resource $result | array',
\ 'mysql_fetch_field(': 'resource $result [, int $field_offset = 0] | object',
\ 'mysql_fetch_lengths(': 'resource $result | array',
\ 'mysql_fetch_object(': 'resource $result [, string $class_name [, array $params]] | object',
\ 'mysql_fetch_row(': 'resource $result | array',
\ 'mysql_field_flags(': 'resource $result, int $field_offset | string',
\ 'mysql_field_len(': 'resource $result, int $field_offset | int',
\ 'mysql_field_name(': 'resource $result, int $field_offset | string',
\ 'mysql_field_seek(': 'resource $result, int $field_offset | bool',
\ 'mysql_field_table(': 'resource $result, int $field_offset | string',
\ 'mysql_field_type(': 'resource $result, int $field_offset | string',
\ 'mysql_free_result(': 'resource $result | bool',
\ 'mysql_get_client_info(': 'void | string',
\ 'mysql_get_host_info(': '[ resource $link_identifier = NULL] | string',
\ 'mysql_get_proto_info(': '[ resource $link_identifier = NULL] | int',
\ 'mysql_get_server_info(': '[ resource $link_identifier = NULL] | string',
\ 'mysql_info(': '[ resource $link_identifier = NULL] | string',
\ 'mysql_insert_id(': '[ resource $link_identifier = NULL] | int',
\ 'mysql_list_dbs(': '[ resource $link_identifier = NULL] | resource',
\ 'mysql_list_fields(': 'string $database_name, string $table_name [, resource $link_identifier = NULL] | resource',
\ 'mysql_list_processes(': '[ resource $link_identifier = NULL] | resource',
\ 'mysql_list_tables(': 'string $database [, resource $link_identifier = NULL] | resource',
\ 'mysql_num_fields(': 'resource $result | int',
\ 'mysql_num_rows(': 'resource $result | int',
\ 'mysql_pconnect(': '[ string $server = ini_get("mysql.default_host") [, string $username = ini_get("mysql.default_user") [, string $password = ini_get("mysql.default_password") [, int $client_flags = 0]]]] | resource',
\ 'mysql_ping(': '[ resource $link_identifier = NULL] | bool',
\ 'mysql_query(': 'string $query [, resource $link_identifier = NULL] | resource',
\ 'mysql_real_escape_string(': 'string $unescaped_string [, resource $link_identifier = NULL] | string',
\ 'mysql_result(': 'resource $result, int $row [, mixed $field = 0] | string',
\ 'mysql_select_db(': 'string $database_name [, resource $link_identifier = NULL] | bool',
\ 'mysql_set_charset(': 'string $charset [, resource $link_identifier = NULL] | bool',
\ 'mysql_stat(': '[ resource $link_identifier = NULL] | string',
\ 'mysql_tablename(': 'resource $result, int $i | string',
\ 'mysql_thread_id(': '[ resource $link_identifier = NULL] | int',
\ 'mysql_unbuffered_query(': 'string $query [, resource $link_identifier = NULL] | resource',
\ })