<?php

global $mysql;

require_once 'config.php';

header('Access-Control-Allow-Origin: *');

if ( empty($_POST['pass']) ) {
  deny_request('No password provided', 403);
  exit;
}

$pass = $_POST['pass'];
if ( $pass != APP_KEY ) {
  deny_request("Invalid password", 403);
  exit;
}

//Process the data.
if ( empty($_POST['data']) ) {
  deny_request("No data to synchronize", 400);
  exit;
}

$data = $_POST['data'];

if ( ! $data || $data == "" || ! is_array($data) ) {
  deny_request("No data to synchronize", 400);
  exit;
}

//Connect to MySQL.
$mysql = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
if (! $mysql ) deny_request('Database error', 500);

$db = mysql_select_db(MYSQL_DB, $mysql);
if ( ! $db ) deny_request('Database select error', 500);

update_entries( $data );


mysql_close($mysql);

echo json_encode( array(
  'status' => 1,
  'message' => 'OK'
) );
exit;

function deny_request($reason,$code = 400) {
  http_response_code($code);
  echo json_encode(array(
    'status' => -1,
    'message' => $reason
  ));
  exit;
}

function update_entries( $data ) {



  foreach ( $data as $trip ) {

    $id = trip_exists($trip['tripId']);

    //Does trip exist?
    if ( $id ) {
      //Update it.
      update_trip($id,$trip);
    } else {
      //Insert it.
      insert_trip($trip);
    }

  }

  return true;

}

function trip_exists( $trip_id ) {

  global $mysql;

  $q = sprintf('SELECT * FROM trips WHERE tripId = %s', mysql_real_escape_string($trip_id) );
  $query = mysql_query( $q, $mysql );

  $res = array();

  while ($row = mysql_fetch_assoc($query)) {
    $res[] = $row;
  }

  if ( count($res) > 0 ) {
    return $res[0]['ID'];
  } else {
    return false;
  }

}

function insert_trip($trip) {

  global $mysql;

  $def = array(
    'start_date' => '01/01/1970 00:00',
    'end_date' => '01/01/1970 00:00',
    'rego' => '',
    'start_odo' => 0,
    'end_odo' => 0,
    'distance' => 0,
    'stat' => 'begin',
    'last_update' => time(),
    'tripId' => 0,
    'purpose' => ''
  );

  foreach ( $trip as $key => $val ) {
    $def[$key] = $val;
  }


  $q = sprintf("INSERT INTO trips (tripId,start_date,end_date,start_odo,end_odo,trip_distance,rego,purpose,status,last_updated) VALUES('%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ",
    mysql_real_escape_string($def['tripId']),
    mysql_real_escape_string($def['start_date']),
    mysql_real_escape_string($def['end_date']),
    mysql_real_escape_string($def['start_odo']),
    mysql_real_escape_string($def['end_odo']),
    mysql_real_escape_string($def['distance']),
    mysql_real_escape_string($def['rego']),
    mysql_real_escape_string($def['purpose']),
    mysql_real_escape_string($def['stat']),
    mysql_real_escape_string($def['last_update'])
  );

  $r = mysql_query($q, $mysql);
  if (! $r ) deny_request('Query error', 500);

  return true;

}

function update_trip($id,$trip) {

  global $mysql;

  $def = array(
    'start_date' => '01/01/1970 00:00',
    'end_date' => '01/01/1970 00:00',
    'rego' => '',
    'start_odo' => 0,
    'end_odo' => 0,
    'distance' => 0,
    'stat' => 'begin',
    'last_update' => time(),
    'tripId' => 0,
    'purpose' => ''
  );

  foreach ( $trip as $key => $val ) {
    $def[$key] = $val;
  }

  $q = sprintf("UPDATE trips SET start_date = '%s', end_date = '%s', start_odo = '%s', end_odo = '%s', trip_distance = '%s', rego = '%s', purpose = '%s', status = '%s', last_updated = '%s' WHERE ID = %s",
    mysql_real_escape_string($def['start_date']),
    mysql_real_escape_string($def['end_date']),
    mysql_real_escape_string($def['start_odo']),
    mysql_real_escape_string($def['end_odo']),
    mysql_real_escape_string($def['distance']),
    mysql_real_escape_string($def['rego']),
    mysql_real_escape_string($def['purpose']),
    mysql_real_escape_string($def['stat']),
    mysql_real_escape_string($def['last_update']),
    mysql_real_escape_string($id)
  );

  $r = mysql_query($q, $mysql);
  if (! $r ) deny_request('Query error update', 500);

  return true;

}
