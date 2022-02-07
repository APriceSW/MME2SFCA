<?php
//ini_set('memory_limit', '1024M');
ini_set('MAX_EXECUTION_TIME', '-1');

class Postgres
{
	/* Set up the database log in information. 
	If possible use a log in system and pass these variables in rather than hard code them into the script.
	*/
	//Set up the username for the database user. 
    private $db_username = '/* username here */';
	//Set up the Password of the user. 
    private $db_password = '/* password here */';
	//Select the correct database on the Postgres server
    private $db_database = '/* database name here */';
	//select Port. 
	private $db_port = '/* port here */';
	//Select host name for database. This can be localhost if Postgres is on the same machine as the PHP scripts. 
    private $db_host = '/* postgres host here */';
    /** @var PDO */
    private $db_connection;

    public function __construct()
    {
        $this->connect();
        $this->db_password = null;
    }

    public function __destruct()
    {
        $this->close();
    }

	//New instance of a PDO using the Class private log in variables. 
    private function connect()
    {
        if (!isset($this->db_connection))
            $this->db_connection = new PDO("pgsql:host=$this->db_host;port=$this->db_port;dbname=$this->db_database",
                $this->db_username, $this->db_password);
    }

    private function close()
    {
        $this->db_connection = null;
    }


    /**
     * @param double $start_lat
     * @param double $start_lon
     * @param double $end_lat
     * @param double $end_lon
     *
     * @return array|bool  [false = fail]
     */
    function get_records($transport_type, $start_lat, $start_lon, $end_lat, $end_lon)
    {
        $stmt = $this->db_connection->prepare('SELECT * FROM "oddistances".' . $transport_type . 'transportdistances WHERE start_lat = ? AND start_lon = ? AND end_lat  = ? AND end_lon = ? ');
        if ($stmt->execute([$start_lat, $start_lon, $end_lat, $end_lon])) {
            $result = $stmt->fetchAll();
            return $result;
        }
        return false;
    }

    /**
     * @param double $start_lat
     * @param double $start_lon
     * @param double $end_lat
     * @param double $end_lon
     * @param integer $distance
     * @param float $duration
     * @param integer $transit_time
     * @param integer $waiting_time
     * @param integer $transfers
     *
     * @return bool
     */
    function insert_record($transport_type, $start_lat, $start_lon, $end_lat, $end_lon, $distance, $duration, $transit_time, $waiting_time, $transfers)
    {
        $stmt = $this->db_connection->prepare('INSERT INTO "oddistances".' . $transport_type . 'transportdistances (start_lat , start_lon , end_lat, end_lon, distance, duration, transit_time, waiting_time, transfers) VALUES (?,?,?,?,?,?,?,?,?)');
        var_dump($stmt);
        return $stmt->execute([$start_lat, $start_lon, $end_lat, $end_lon, $distance, $duration, $transit_time, $waiting_time, $transfers]);
    }

	//Truncate all of the public transport distances tables. This is usually initiated once the OD Matrix is filled with the journey times and distances. 
    /**
     * @return bool
     */
    function truncate_records()
    {
        $stmt = $this->db_connection->prepare('TRUNCATE TABLE "oddistances".publictransportdistances');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('TRUNCATE TABLE "oddistances".privatetransportdistances');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('TRUNCATE TABLE "oddistances".publictransportdistancesmin');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('TRUNCATE TABLE "oddistances".publictransporttimemin');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('TRUNCATE TABLE "oddistances".cyclingtransportdistances');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('TRUNCATE TABLE "oddistances".walkingtransportdistances');
        $result = $stmt->execute();
        return $result;
    }

	//Initiate a filter on public transport for shortest distances to be extracted from the "publictransportdistancesmin" and "publictransporttimemin" tables. 
    /**
    * @return bool
    */
    function filter_shortest()
    {
        $stmt = $this->db_connection->prepare('DROP TABLE "oddistances".publictransportdistancesmin;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('create table "oddistances".publictransportdistancesmin as (select start_lat, start_lon, end_lat, end_lon, MIN(distance) from "oddistances".publictransportdistances group by start_lat, start_lon, end_lat, end_lon);');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('DROP TABLE "oddistances".publictransporttimemin;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('create table "oddistances".publictransporttimemin as (select start_lat, start_lon, end_lat, end_lon, MIN(duration) as mintime from "oddistances".publictransportdistances group by start_lat, start_lon, end_lat, end_lon);');
        $result = $stmt->execute();
        return $result;
    }

	//Returns a list of tables within a specified schema. Eg. All the OD matrices within the "ODmatrices" schema. 
	/**
    * @param string $SchemaName
    *
    * @return array|bool  [false = fail]
    */
    function get_tables($SchemaName)
    {
        $stmt = $this->db_connection->prepare("SELECT * FROM pg_catalog.pg_tables  where schemaname = '" . $SchemaName . "';");
        $result = $stmt->execute();
        if ($result) {
            $return = array();
            while ($table = $stmt->fetch()) {
                $return[] = $table['tablename'];
            }
            return $return;
        } else
            return false;

    }

	//Returns a list of table names within a specified table in a date order from the table: "odmatricesdetails".odmatricesdetails
	/**
    *
    * @return array|bool  [false = fail]
    */
    function get_tables_in_date_order_recent()
    {
        $stmt = $this->db_connection->prepare('SELECT * FROM "odmatricesdetails".odmatricesdetails order by datetime desc;' );
        $result = $stmt->execute();
        if ($result) {
            $return = array();
            while ($table = $stmt->fetch()) {
                $return[] = $table['tablename'];
            }
            return $return;
        } else
            return false;
    }

	//Returns a list of map results within a specified schema in a date order from the table: "odmatricesdetails".accessibilityresultsdetails
	/**
    *
    * @return array|bool  [false = fail]
    */
    function get_maps_in_date_order_recent()
    {
        $stmt = $this->db_connection->prepare('SELECT * FROM "odmatricesdetails".accessibilityresultsdetails order by datetime desc;' );
        $result = $stmt->execute();
        if ($result) {
            $return = array();
            while ($table = $stmt->fetch()) {
                $return[] = $table['calculationtablename'];
            }
            return $return;
        } else
            return false;
    }

	//Returns an OD matrix from the Postgres Database. 
	/**
    * @param string $tablename
    *
    * @return array
    */
    function get_OD_matrix($tablename)
    {
        $stmt = $this->db_connection->prepare('SELECT * FROM "ODmatrices".' . $tablename . ' ;');
        $stmt->execute();
        $return = $stmt->fetchAll();
        return $return;

    }
	
	//Returns a list of columns from a table. 
	/**
    * @param string $tablename
    * @param string $dataType
    *
    * @return array|bool  [false = fail]
    */
    function get_columns($tablename, $datatype)
    {
        $stmt = $this->db_connection->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = '" . $tablename . "' and DATA_TYPE = '" . $datatype . "';");
        $result = $stmt->execute();
        if ($result) {
            $return = array();
            while ($column = $stmt->fetch()) {
                $return[] = $column['column_name'];
            }
            return $return;
        } else
            return false;

    }

	//Set a decay function column for the MM2SFCA calculation. Add when a different decay is added a different type of column is needed for reference. 
	/**
    * @param string $SupTable
    * @param string $DecayType
	*
    */
    function set_decay($SupTable, $DecayType)
    {
        if ($DecayType == 'No Decay') {
            $stmt = $this->db_connection->prepare('alter table "supplytables".' . $SupTable . ' drop column if exists akc;');
            $stmt->execute();
            $stmt = $this->db_connection->prepare('alter table "supplytables".' . $SupTable . ' add column akc float;');
            $stmt->execute();
        } elseif ($DecayType == 'Linear Decay') {
            $stmt = $this->db_connection->prepare('alter table "supplytables".' . $SupTable . ' drop column if exists ake;');
            $stmt->execute();
            $stmt = $this->db_connection->prepare('alter table "supplytables".' . $SupTable . ' add column ake float;');
            $stmt->execute();
        }
    }

	/********************************************************************************************************************************************
	
	Original E2SFCA testing functions. Remain within system for reference or comparing MME2SFCA within original E2SFCA accessibility models. 
	
	*******************************************************************************************************************************************/
 /*	function FCA_step_one($SupTable, $ODTable, $CatchmentSize, $DecayType)
    {
        $stmt = $this->db_connection->prepare('select id as supid, snapid, capacity as supvol from "supplytables".' . $SupTable . ' order by id;');
        $result = $stmt->execute();

        if ($DecayType == 'No Decay') {
            if ($result) {
                $return = array();
                while ($row = $stmt->fetch()) {
                    $fcastmt = $this->db_connection->prepare('select sum(demvol) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and privateduration between 0 and ' . $CatchmentSize . ';');
                    $fcastmt->execute();
                    $fcaresult = $fcastmt->fetchAll();
                    $fcastmttwo = $this->db_connection->prepare('update "supplytables".' . $SupTable . ' set akc = cast(' . $row['supvol'] . ' as float) / cast(' . $fcaresult[0]['sum'] . ' as float) where id  = ' . $row['supid'] . ';');
                    $fcastmttwo->execute();
                }
                return $return;
            } else
                return false;
        } elseif ($DecayType == 'Linear Decay') {
            if ($result) {
                $return = array();
                while ($row = $stmt->fetch()) {
                    $fcastmt = $this->db_connection->prepare('select sum(cast(demvol as double precision) * (1.0 - (privateduration / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and privateduration between 0 and ' . $CatchmentSize . ';');
                    $fcastmt->execute();
                    $fcaresult = $fcastmt->fetchAll();
                    $fcastmttwo = $this->db_connection->prepare('update "supplytables".' . $SupTable . ' set ake = cast(' . $row['supvol'] . ' as float) / cast(' . $fcaresult[0]['sum'] . ' as float) where id  = ' . $row['supid'] . ';');

                    $fcastmttwo->execute();
                }
                return $return;
            } else
                return false;
        }
    }
    function FCA_step_two($SupTable, $ODTable, $CatchmentSize, $DemTable, $FCAResultName, $DecayType)
    {
        $stmt = $this->db_connection->prepare('drop table if exists demtotals;');
        $stmt->execute();

        if ($DecayType == 'No Decay') {
            $stmt = $this->db_connection->prepare('create table demtotals as ( select od.demid, sum(sup.akc) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and privateduration between 0 and ' . $CatchmentSize . ' group by od.demid);');
            $stmt->execute();
        } elseif ($DecayType == 'Linear Decay') {
            $stmt = $this->db_connection->prepare('create table demtotals as ( select od.demid, sum(sup.ake * (1.0 - (privateduration / ' . $CatchmentSize . '::real))) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and privateduration between 0 and ' . $CatchmentSize . ' group by od.demid);');
            $stmt->execute();
        }

        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' drop column if exists ' . $FCAResultName . ';');
        $stmt->execute();

        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' add column ' . $FCAResultName . ' float;');
        $result = $stmt->execute();

        return $result;
    }

    function add_fca_to_demand($DemTable, $FCAResultName)
    {
        $stmt = $this->db_connection->prepare('update "demandtables".' . $DemTable . ' set ' . $FCAResultName . ' = demtotals.ak_total from demtotals where demtotals.demid = id;');
        //var_dump($stmt);
        $result = $stmt->execute();
        if ($result) {
            return true;
        } else
            return false;
    }
*/


	//Generates a OD matrix with parameters set by the user on the ODBuilder page. 
	/**
    * @param string $sldValue
    * @param string $SUPTable
    * @param string $SUPId
    * @param string $SUPGeom
    * @param string $SUPVol
    * @param string $DEMTable
    * @param string $DEMId
    * @param string $DEMGeom
    * @param string $DEMVol
    * @param string $AGERanges
    *
    * @return bool  [false = fail]
    */
    function create_OD($sldValue, $SUPTable, $SUPId, $SUPGeom, $SUPVol, $DEMTable, $DEMId, $DEMGeom, $DEMVol, $AGERanges)
    {
		//Generate a string to combine specific age ranges within the Demand tables. 
        $agerangestring = "";
        $ageranges = explode(',', $AGERanges);
        $ageswithtable = array();

        foreach ($ageranges as $key => $value){
            array_push($ageswithtable, 'demand.' . $value);
        }
        $lastElement = array_key_last($ageswithtable);

        foreach ($ageswithtable as $key => $value){
            if($key == $lastElement){
                $agerangestring .= $value;
            }
            else{
                $agerangestring .= $value . " + ";
            }
        }


		//Delete the OD matrix if it already exists so there is no conflict with generating another table with the same stats. 
        $stmt = $this->db_connection->prepare('drop table if exists "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue);
        $stmt->execute();

		//Generate and execute the SQL to create an OD matrix table. 
        $stmt = $this->db_connection->prepare(
            'create table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' as (' .
            'select supply.' . $SUPId . ' as supid, supply.' . $SUPVol . ' as supvol, ' .
            'demand.' . $DEMId . ' as demid, ' . $agerangestring . ' as demvol, ' .
            'st_distance(supply.' . $SUPGeom . ', demand.' . $DEMGeom . ') as sld, ' .
            'CAST(ST_Y(st_transform(demand.' . $DEMGeom . ', 4326)) as numeric(10,8)) as start_lat, ' .
            'CAST(ST_X(st_transform(demand.' . $DEMGeom . ', 4326)) as numeric(10,8)) as  start_lon, ' .
            'CAST(ST_Y(st_transform(supply.' . $SUPGeom . ', 4326)) as numeric(10,8)) as end_lat, ' .
            'CAST(ST_X(st_transform(supply.' . $SUPGeom . ', 4326)) as numeric(10,8)) as  end_lon ' .
            'from "supplytables".' . $SUPTable . ' as supply ' .
            'join "demandtables".' . $DEMTable . ' as demand on st_dwithin(supply.' . $SUPGeom . ', demand.' . $SUPGeom . ', ' . $sldValue . ')' .
            'order by supid, sld);'
        );
        $stmt->execute();

		//Build strings and set variables to enter details of the newly created OD matrix table into a table for OD matrix details. 
        $TableName = "'" . $SUPTable . "_" . $DEMTable . "_" . $sldValue . "'";
        $savedages = "'" . $AGERanges . "'";
        $savedcatchment = "'" . $sldValue . "'";
        $savedsupply = "'" . $SUPTable . "'";
        $savedcdemand = "'" . $DEMTable . "'";
        $builddate = "'builddate'";

		//Get the road network build date from the database. 
        $networkstmt = $this->db_connection->prepare('Select networkdate from odmatricesdetails.roadnetworkdate where versiontype = ' .  $builddate . ';');
        $networkstmt->execute();
        $roadnetworkdate = $networkstmt->fetch();
        $roadnetwork = "'" . $roadnetworkdate['networkdate'] . "'";

		//Get the supply table version date from the database. 
        $versionstmt = $this->db_connection->prepare('Select * from "odmatricesdetails".supplyversioncontrol where tablename = ' . $savedsupply . ';');
        $versionstmt->execute();
        $supplyversioninfo = $versionstmt->fetch();
        $supplydate = "'" . $supplyversioninfo['tabledate'] . "'";
        $supplyversion = "'" . $supplyversioninfo['supplytableversion'] . "'";

		//Delete the OD matrix inforamtion from the OD matrixes details table if it already exists. 
        $stmt = $this->db_connection->prepare('DELETE FROM "odmatricesdetails".odmatricesdetails WHERE tablename = ' . $TableName . ';');
        $stmt->execute();

		//Insert newly generated details into OD matrixes details table. 
        $stmt = $this->db_connection->prepare('INSERT INTO  "odmatricesdetails".odmatricesdetails (tablename, agerange, catchmentsize, facilitytype, demandtype, supplytabledate, roadnetworkdate, supplytableversion)
            Values(' . $TableName . ',' . $savedages . ',' . $savedcatchment . ',' . $savedsupply . ',' . $savedcdemand . ',' . $supplydate . ',' . $roadnetwork . ',' . $supplyversion . ');');
        $stmt->execute();

		//Add new columns for the times and distances for each mode of transport. This will need to be edited with every mode of transport, and should be upgraded to a design pattern.
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column privatedistance integer;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column publicdistance integer;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column cycledistance integer;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column walkingdistance integer;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column publicduration integer;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column privateduration integer;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column cycleduration integer;');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "ODmatrices".' . $SUPTable . '_' . $DEMTable . '_' . $sldValue . ' add column walkingduration integer;');
        $result = $stmt->execute();

		//If result is successful then return true status. 
        if ($result) {
            return true;
        } else
            return false;
    }


	//Applies all the journey distances and times generated from OTP to the OD matrix table. This requires additional work when more modes of transport are considered and should be upgraded to a design pattern. 
	/**
    * @param string $od_matrix
    *
    * @return bool  [false = fail]
    */
    function join_tables($od_matrix)
    {
		//Apply the distances from each journey table to the OD matrix using the lat and long combinations. 
        $stmt = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set publicdistance = publictransportdistancesmin.min ' .
            'from  "oddistances".publictransportdistancesmin ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".publictransportdistancesmin.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".publictransportdistancesmin.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".publictransportdistancesmin.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".publictransportdistancesmin.end_lon;');
        $stmt->execute();

        $stmttwo = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set privatedistance = privatetransportdistances.distance ' .
            'from  "oddistances".privatetransportdistances ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".privatetransportdistances.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".privatetransportdistances.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".privatetransportdistances.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".privatetransportdistances.end_lon;');
        $stmttwo->execute();

        $stmtbikedistance = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set cycledistance = cyclingtransportdistances.distance ' .
            'from  "oddistances".cyclingtransportdistances ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".cyclingtransportdistances.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".cyclingtransportdistances.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".cyclingtransportdistances.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".cyclingtransportdistances.end_lon;');
        $stmtbikedistance->execute();

        $stmtwalkdistance = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set walkingdistance = walkingtransportdistances.distance ' .
            'from  "oddistances".walkingtransportdistances ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".walkingtransportdistances.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".walkingtransportdistances.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".walkingtransportdistances.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".walkingtransportdistances.end_lon;');
        $stmtwalkdistance->execute();

        $stmtthree = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set publicduration = publictransporttimemin.mintime ' .
            'from  "oddistances".publictransporttimemin ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".publictransporttimemin.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".publictransporttimemin.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".publictransporttimemin.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".publictransporttimemin.end_lon;');
        $stmtthree->execute();

        $stmtfour = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set privateduration = privatetransportdistances.duration ' .
            'from  "oddistances".privatetransportdistances ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".privatetransportdistances.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".privatetransportdistances.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".privatetransportdistances.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".privatetransportdistances.end_lon;');
        $stmtfour->execute();

        $stmtcyclingtime = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set cycleduration = cyclingtransportdistances.duration ' .
            'from  "oddistances".cyclingtransportdistances ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".cyclingtransportdistances.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".cyclingtransportdistances.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".cyclingtransportdistances.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".cyclingtransportdistances.end_lon;');
        $stmtcyclingtime->execute();

        $stmtwalkingtime = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set walkingduration = walkingtransportdistances.duration ' .
            'from  "oddistances".walkingtransportdistances ' .
            'where "ODmatrices".' . $od_matrix . '.start_lat = "oddistances".walkingtransportdistances.start_lat ' .
            'and "ODmatrices".' . $od_matrix . '.start_lon = "oddistances".walkingtransportdistances.start_lon ' .
            'and "ODmatrices".' . $od_matrix . '.end_lat = "oddistances".walkingtransportdistances.end_lat ' .
            'and "ODmatrices".' . $od_matrix . '.end_lon = "oddistances".walkingtransportdistances.end_lon;');
        $stmtwalkingtime->execute();


		//If there are no journey times generated for a particular origin-to-destination for any mode then set the distance to 999,999 and the time to 999.0
		//Usually means that a journey from one point to another is not possible or the node has not snapped to the network. 
		//If a particular supply point and demand point constantly sets to these values then mode the location closer to a road. 
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set privateduration = 999.0 where privateduration is NULL;');
        $stmtfinal->execute();
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set publicduration = 999.0 where publicduration is NULL;');
        $stmtfinal->execute();
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set cycleduration = 999.0 where cycleduration is NULL;');
        $stmtfinal->execute();
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set walkingduration = 999.0 where walkingduration is NULL;');
        $stmtfinal->execute();
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set privatedistance = 999999 where privatedistance is NULL;');
        $stmtfinal->execute();
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set publicdistance = 999999 where publicdistance is NULL;');
        $stmtfinal->execute();
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set cycledistance = 999999 where cycledistance is NULL;');
        $stmtfinal->execute();
        $stmtfinal = $this->db_connection->prepare('Update "ODmatrices".' . $od_matrix . ' set walkingdistance = 999999 where walkingdistance is NULL;');
        $result = $stmtfinal->execute();

		//return result 
        if ($result) {
            return true;
        } else
            return false;
    }

	/********************************************************************************************************************************************
	
	Accessibility models here, currently using MM2SFCA from Langford et al. (2016). 
	-- The local modal split is work prior to Thesis contributions and extensions from the future work. 
	
	*******************************************************************************************************************************************/
	//Set 1 of the MM2SFCA from Langford et al. (2016)
	/**
    * @param string $SupTable
    * @param string $ODTable
    * @param string $CatchmentSize
    * @param string $DecayType
    * @param string $BusPercentage
    * @param string $CarPercentage
    * @param string $BikePercentage
    * @param string $WalkPercentage
    * @param string $MeasurementType
    * @param string $SplitOption
    *
    * @return bool  [false = fail]
    */
    function MM_FCA_step_one($SupTable, $ODTable, $CatchmentSize, $DecayType, $BusPercentage, $CarPercentage, $BikePercentage, $WalkPercentage, $MeasurementType, $SplitOption)
    {
        $stmt = $this->db_connection->prepare('select id as supid, capacity as supvol from "supplytables".' . $SupTable. ' order by id;');
        //$stmt = $this->db_connection->prepare('select id as supid, totalhours as supvol from "supplytables".' . $SupTable. ' order by id;');
        $result = $stmt->execute();

		if($SplitOption == 'localmodalsplit'){
			if ($DecayType == 'No Decay') {
				if ($result) {
					while ($row = $stmt->fetch()) {
						$fcastmt = $this->db_connection->prepare('select carpopulation from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and private' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$fcastmt->execute();
						$fcacarresult = $fcastmt->fetchAll();
						$busstmt = $this->db_connection->prepare('select buspopulation from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and public' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$busstmt->execute();
						$fcabusresult = $busstmt->fetchAll();
						$bikestmt = $this->db_connection->prepare('select sum(carpopulation * 0) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and cycle' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$bikestmt->execute();
						$fcabikeresult = $bikestmt->fetchAll();
						$walkstmt = $this->db_connection->prepare('select sum(carpopulation * 0) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and walking' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$walkstmt->execute();
						$fcawalkresult = $walkstmt->fetchAll();
						$fcastmttwo = $this->db_connection->prepare('update "supplytables".' . $SupTable . ' set akc = cast(' . $row['supvol'] . ' as float) / (cast(' . $fcacarresult[0]['sum'] . ' as float) + cast(' . $fcabusresult[0]['sum'] . ' as float) + cast(' . $fcabikeresult[0]['sum'] . ' as float) + cast(' . $fcawalkresult[0]['sum'] . ' as float)) where id  = ' . $row['supid'] . ';');
						$fcastmttwo->execute();
					}
					return true;
				} else
					return false;
			}
			if ($DecayType == 'Linear Decay') {
				if ($result) {
					while ($row = $stmt->fetch()) {
						//$fcastmt = $this->db_connection->prepare('select sum(cast(carpopulation * 0.9 as double precision) * (1.0 - (private' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and private' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$fcastmt = $this->db_connection->prepare('select sum(carpopulation  * (1.0 - (private' . $MeasurementType . ' / (' . $CatchmentSize . '::real)/2))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and private' . $MeasurementType . ' > 0 and private' . $MeasurementType . ' <= (' . $CatchmentSize . ' / 2);');
						//$fcastmt = $this->db_connection->prepare('select sum(demvol  * (1.0 - (private' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and private' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$fcastmt->execute();
						$fcacarresult = $fcastmt->fetchAll();
						//var_dump($fcacarresult);
						$busstmt = $this->db_connection->prepare('select sum(buspopulation * (1.0 - (public' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and public' . $MeasurementType . ' > 0 and public' . $MeasurementType . ' <= ' . $CatchmentSize . ';');
						//$busstmt = $this->db_connection->prepare('select sum(0 * (1.0 - (public' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and public' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$busstmt->execute();
						$fcabusresult = $busstmt->fetchAll();
						//$bikestmt = $this->db_connection->prepare('select sum(cast(carpopulation * 0.01 as double precision) * (1.0 - (cycle' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and cycle' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$bikestmt = $this->db_connection->prepare('select sum(0 * (1.0 - (cycle' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and cycle' . $MeasurementType . ' > 0 and cycle' . $MeasurementType . ' <= ' . $CatchmentSize . ';');
						$bikestmt->execute();
						$fcabikeresult = $bikestmt->fetchAll();
						//$walkstmt = $this->db_connection->prepare('select sum(cast(carpopulation * 0.09 as double precision) * (1.0 - (walking' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and walking' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$walkstmt = $this->db_connection->prepare('select sum(0 * (1.0 - (walking' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and walking' . $MeasurementType . ' > 0 and walking' . $MeasurementType . ' <= ' . $CatchmentSize . ';');
						$walkstmt->execute();
						$fcawalkresult = $walkstmt->fetchAll();
						//$fcastmttwo = $this->db_connection->prepare('update "supplytables".' . $SupTable . ' set ake = cast(' . $row['supvol'] . ' as float) / ( cast(' . $fcacarresult[0]['sum'] . ' as float) + cast(' . $fcabusresult[0]['sum'] . ' as float) + cast(' . $fcabikeresult[0]['sum'] . ' as float) + cast(' . $fcawalkresult[0]['sum'] . ' as float)) where id  = ' . $row['supid'] . ';');
						//only car and bus. 
						$fcastmttwo = $this->db_connection->prepare('update "supplytables".' . $SupTable . ' set ake = cast(' . $row['supvol'] . ' as float) / ( cast(' . $fcacarresult[0]['sum'] . ' as float) + cast(' . $fcabusresult[0]['sum'] . ' as float) ) where id  = ' . $row['supid'] . ';');
						$fcastmttwo->execute();
						var_dump($fcastmttwo);
					}
					return true;

				} else
					return false;

			}
		}
		if($SplitOption == 'globalmodalsplit'){
			if ($DecayType == 'No Decay') {
				if ($result) {
					//$return = array();
					while ($row = $stmt->fetch()) {
						$fcastmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $CarPercentage .  ' / 100.0) as double precision)) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and private' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$fcastmt->execute();
						$fcacarresult = $fcastmt->fetchAll();
						echo $fcacarresult;
						$busstmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $BusPercentage .  ' / 100.0) as double precision)) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and public' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$busstmt->execute();
						$fcabusresult = $busstmt->fetchAll();
						$bikestmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $BikePercentage .  ' / 100.0) as double precision)) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and cycle' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$bikestmt->execute();
						$fcabikeresult = $bikestmt->fetchAll();
						$walkstmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $WalkPercentage .  ' / 100.0) as double precision)) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and walking' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ';');
						$walkstmt->execute();
						$fcawalkresult = $walkstmt->fetchAll();
						$fcastmttwo = $this->db_connection->prepare('update "supplytables".' . $SupTable . ' set akc = cast(' . $row['supvol'] . ' as float) / (cast(' . $fcacarresult[0]['sum'] . ' as float) + cast(' . $fcabusresult[0]['sum'] . ' as float) + cast(' . $fcabikeresult[0]['sum'] . ' as float) + cast(' . $fcawalkresult[0]['sum'] . ' as float)) where id  = ' . $row['supid'] . ';');
						$fcastmttwo->execute();
					}
					return true;
				} else
					return false;
			}
			if ($DecayType == 'Linear Decay') {
				if ($result) {
					//$return = array();
					while ($row = $stmt->fetch()) {
						//var_dump($CarPercentage);
						$fcastmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $CarPercentage .  ' / 100.0) as double precision) * (1.0 - (private' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and private' . $MeasurementType . ' > 0 and private' . $MeasurementType . ' <= ' . $CatchmentSize . ';');
						$fcastmt->execute();
						$fcacarresult = $fcastmt->fetchAll();
						//var_dump($fcacarresult);
						$busstmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $BusPercentage .  ' / 100.0) as double precision) * (1.0 - (public' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and public' . $MeasurementType . ' > 0 and public' . $MeasurementType . ' <= ' . $CatchmentSize . ';');
						$busstmt->execute();
						$fcabusresult = $busstmt->fetchAll();
						$bikestmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $BikePercentage .  ' / 100.0) as double precision) * (1.0 - (cycle' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and cycle' . $MeasurementType . ' > 0 and cycle' . $MeasurementType . ' <= ' . $CatchmentSize . ';');
						$bikestmt->execute();
						$fcabikeresult = $bikestmt->fetchAll();
						$walkstmt = $this->db_connection->prepare('select sum(cast(demvol * (' . $WalkPercentage .  ' / 100.0) as double precision) * (1.0 - (walking' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) from "ODmatrices".' . $ODTable . ' where supid = ' . $row['supid'] . ' and walking' . $MeasurementType . ' > 0 and walking' . $MeasurementType . ' <= ' . $CatchmentSize . ';');
						$walkstmt->execute();
						$fcawalkresult = $walkstmt->fetchAll();
						$fcastmttwo = $this->db_connection->prepare('update "supplytables".' . $SupTable . ' set ake = cast(' . $row['supvol'] . ' as float) / ( cast(' . $fcacarresult[0]['sum'] . ' as float) + cast(' . $fcabusresult[0]['sum'] . ' as float) + cast(' . $fcabikeresult[0]['sum'] . ' as float) + cast(' . $fcawalkresult[0]['sum'] . ' as float)) where id  = ' . $row['supid'] . ';');
						$test = $fcastmttwo->execute();
						//var_dump($test);
						//var_dump($fcastmttwo);
					}
					return true;

				} else
					return false;

			}
		}
    }

	//Set 2 of the MM2SFCA from Langford et al. (2016)
	/**
    * @param string $SupTable
    * @param string $ODTable
    * @param string $CatchmentSize
    * @param string $DemTable
    * @param string $FCAPublicResultName
    * @param string $FCAPrivateResultName
    * @param string $FCABikeResultName
    * @param string $FCAWalkResultName
    * @param string $DecayType
    * @param string $MeasurementType
    *
    * @return bool  [false = fail]
    */
    function MM_FCA_step_two($SupTable, $ODTable, $CatchmentSize, $DemTable, $FCAPublicResultName, $FCAPrivateResultName, $FCABikeResultName ,$FCAWalkResultName , $DecayType, $MeasurementType)
    {
		//Create delete existing demand totals tables. 
        $stmt = $this->db_connection->prepare('drop table if exists publicdemtotals;');
        $test = $stmt->execute();

        $stmt = $this->db_connection->prepare('drop table if exists privatedemtotals;');
        $test = $stmt->execute();

        $stmt = $this->db_connection->prepare('drop table if exists bikedemtotals;');
        $test = $stmt->execute();

        $stmt = $this->db_connection->prepare('drop table if exists walkdemtotals;');
        $test = $stmt->execute();

		//Set equations based on decay function. 
        if ($DecayType == 'No Decay') {
            $stmt = $this->db_connection->prepare('create table publicdemtotals as ( select od.demid, sum(sup.akc) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and public' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ' group by od.demid);');
            $stmt->execute();
            
			$stmt = $this->db_connection->prepare('create table privatedemtotals as ( select od.demid, sum(sup.akc) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and private' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ' group by od.demid);');
            $result = $stmt->execute();
            
			$stmt = $this->db_connection->prepare('create table bikedemtotals as ( select od.demid, sum(sup.akc) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and cycle' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ' group by od.demid);');
            $result = $stmt->execute();
            
			$stmt = $this->db_connection->prepare('create table walkdemtotals as ( select od.demid, sum(sup.akc) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and walking' . $MeasurementType . ' between 0 and ' . $CatchmentSize . ' group by od.demid);');
            $result = $stmt->execute();
        } elseif ($DecayType == 'Linear Decay') {
            $stmt = $this->db_connection->prepare('create table publicdemtotals as ( select od.demid, sum(sup.ake * (1.0 - (public' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and public' . $MeasurementType . ' > 0 and public' . $MeasurementType . ' <= ' . $CatchmentSize . ' group by od.demid);');
            $stmt->execute();
			/////localmodal split. 
            //$stmt = $this->db_connection->prepare('create table privatedemtotals as ( select od.demid, sum(sup.ake * (1.0 - (private' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and private' . $MeasurementType . ' > 0 and private' . $MeasurementType  . ' <= ' . $CatchmentSize . ' group by od.demid);');
			$stmt = $this->db_connection->prepare('create table privatedemtotals as ( select od.demid, sum(sup.ake * (1.0 - (private' . $MeasurementType . ' / (' . $CatchmentSize . '/2)::real))) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and private' . $MeasurementType . ' > 0 and private' . $MeasurementType . ' <= (' . $CatchmentSize . '/2) group by od.demid);');
            $result = $stmt->execute();
            
			$stmt = $this->db_connection->prepare('create table bikedemtotals as ( select od.demid, sum(sup.ake * (1.0 - (cycle' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and cycle' . $MeasurementType . ' > 0 and cycle' . $MeasurementType  . ' <= ' . $CatchmentSize . ' group by od.demid);');
            $result = $stmt->execute();
            
			$stmt = $this->db_connection->prepare('create table walkdemtotals as ( select od.demid, sum(sup.ake * (1.0 - (walking' . $MeasurementType . ' / ' . $CatchmentSize . '::real))) as ak_total from "ODmatrices".' . $ODTable . ' as od join "supplytables".' . $SupTable . ' as sup on od.supid = sup.id and walking' . $MeasurementType . ' > 0 and walking' . $MeasurementType  . ' <= ' . $CatchmentSize . ' group by od.demid);');
            $result = $stmt->execute();

        }

		//remove old versions of the scores and create columns for new FCA scores. 
        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' drop column if exists ' . $FCAPublicResultName . ';');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' add column ' . $FCAPublicResultName . ' float;');
        $result = $stmt->execute();

        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' drop column if exists ' . $FCAPrivateResultName . ';');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' add column ' . $FCAPrivateResultName . ' float;');
        $result = $stmt->execute();

        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' drop column if exists ' . $FCABikeResultName . ';');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' add column ' . $FCABikeResultName . ' float;');
        $result = $stmt->execute();

        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' drop column if exists ' . $FCAWalkResultName . ';');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('alter table "demandtables".' . $DemTable . ' add column ' . $FCAWalkResultName . ' float;');
        $result = $stmt->execute();
        //var_dump($result);
        if ($result) {
            return true;
        } else
            return false;
    }

	//Add the 2SFCA scores to the demand table. 
	/**
    * @param string $DemTable
    * @param string $FCAPublicResultName
    * @param string $FCAPrivateResultName
    * @param string $FCABikeResultName
    * @param string $FCAWalkResultName
    *
    * @return bool  [false = fail]
    */
    function MM_add_fca_to_demand($DemTable, $FCAPublicResultName, $FCAPrivateResultName, $FCABikeResultName, $FCAWalkResultName)
    {
        $stmt = $this->db_connection->prepare('update "demandtables".' . $DemTable . ' set ' . $FCAPublicResultName . ' = publicdemtotals.ak_total from publicdemtotals where publicdemtotals.demid = id;');
        $stmt->execute();
        
		$stmt = $this->db_connection->prepare('update "demandtables".' . $DemTable . ' set ' . $FCAPrivateResultName . ' = privatedemtotals.ak_total from privatedemtotals where privatedemtotals.demid = id;');
        $result = $stmt->execute();
        
		$stmt = $this->db_connection->prepare('update "demandtables".' . $DemTable . ' set ' . $FCABikeResultName . ' = bikedemtotals.ak_total from bikedemtotals where bikedemtotals.demid = id;');
        $result = $stmt->execute();
        
		$stmt = $this->db_connection->prepare('update "demandtables".' . $DemTable . ' set ' . $FCAWalkResultName . ' = walkdemtotals.ak_total from walkdemtotals where walkdemtotals.demid = id;');
        $result = $stmt->execute();
		
        if ($result) {
            return true;
        } else
            return false;
    }

	//Combine the results from the demand tables column and the polygon data column. 
	/**
    * @param string $CalculationName
    * @param string $DemTable
    * @param string $FCAPublicResultName
    * @param string $FCAPrivateResultName
    * @param string $FCABikeResultName
    * @param string $FCAWalkResultName
    *
    * @return bool  [false = fail]
    */
    function add_to_polygon($CalculationName, $DemTable, $FCAPublicResultName, $FCAPrivateResultName, $FCABikeResultName, $FCAWalkResultName)
    {
		//If the results have already been produced and a new one is replacing it then remove it. 
        $stmt = $this->db_connection->prepare('drop table if exists "webmap".'. $CalculationName . ';');
        $stmt->execute();

		//Build the string based on the geographic area being applied to. 
		//change to b.totalpop if using total population column or mid year estimates for mid year estimates column. 
        if ($DemTable == "oa_populated_centroids"){
            $stmt = $this->db_connection->prepare('create table "webmap".'. $CalculationName . ' as (SELECT
                    a.id as oaid,
                    a."OA11CD" as oagss,
                    a.geom as geom, 
                    b.geom as centroid,
                    b.totalpop , 
                    b.' . $FCAPublicResultName . ',
                    b.' . $FCAPrivateResultName . ',
                    b.' . $FCABikeResultName . ',
                    b.' . $FCAWalkResultName . '
                FROM demandtables."OApolygons" as a 
                INNER JOIN demandtables.oa_populated_centroids as b 
                ON a."OA11CD" = b."gss");');
            $result = $stmt->execute();

        }elseif($DemTable == "lsoa_populated_centroids"){
            $stmt = $this->db_connection->prepare('create table "webmap".'. $CalculationName . ' as (SELECT
                    a.id as lsoaid,
                    a."LSOA11CD" as lsoagss,
                    a.geom as geom, 
                    b.geom as centroid,
                    b.totalpop , 
                    b.' . $FCAPublicResultName . ',
                    b.' . $FCAPrivateResultName . ',
                    b.' . $FCABikeResultName . ',
                    b.' . $FCAWalkResultName . '
                FROM demandtables."LSOApolygons" as a 
                INNER JOIN demandtables.lsoa_populated_centroids as b 
                ON a."LSOA11CD" = b."gss");');
            $result = $stmt->execute();

        }elseif($DemTable == "msoa_populated_centroids"){
            $stmt = $this->db_connection->prepare('create table "webmap".'. $CalculationName . ' as (SELECT
                    a.id as lsoaid,
                    a."msoa11cd" as msoagss,
                    a.geom as geom, 
                    b.geom as centroid,
                    b.totalpop , 
                    b.' . $FCAPublicResultName . ',
                    b.' . $FCAPrivateResultName . ',
                    b.' . $FCABikeResultName . ',
                    b.' . $FCAWalkResultName . '
                FROM demandtables."MSOApolygons" as a 
                INNER JOIN demandtables.msoa_populated_centroids as b 
                ON a."msoa11cd" = b."gss");');
            $result = $stmt->execute();

        }

		//Update the results for the web map to show 0 score for areas where no score was generated. This will prevent polygons with no accessibility score from dissapearing and not being displayed on the webmap. 
        $stmt = $this->db_connection->prepare('update "webmap".' . $CalculationName . ' set ' . $FCAPublicResultName . ' = 0 where ' . $FCAPublicResultName . ' is null; ');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('update "webmap".' . $CalculationName . ' set ' . $FCAPrivateResultName . ' = 0 where ' . $FCAPrivateResultName . ' is null; ');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('update "webmap".' . $CalculationName . ' set ' . $FCABikeResultName . ' = 0 where ' . $FCABikeResultName . ' is null; ');
        $stmt->execute();
        $stmt = $this->db_connection->prepare('update "webmap".' . $CalculationName . ' set ' . $FCAWalkResultName . ' = 0 where ' . $FCAWalkResultName . ' is null; ');
        $result = $stmt->execute();

		//return result of executions. 
        if ($result) {
            return true;
        } else
            return false;
    }

	//Returns the layers for the polygon maps. 
	/**
    * @param string $sql
    *
    * @return bool  [false = fail]
    */
    function get_mapLayers($sql){

        $stmt = $this->db_connection->prepare('' . $sql . '');
        $result = $stmt->execute();

        if ($result) {
            //$return = array();
            while ($row = $stmt->fetch()) {
                echo $row[0] . "!! ";
                echo $row[1] . "!! ";
                echo $row[2] . "!! ";
                echo $row[3] . "!! ";
                echo $row[4] . "!! ";
                echo ";";
            }
            return true;
        } else
            return false;

    }

	//Returns the layers for the point maps. 
	/**
    * @param string $sql
    *
    * @return bool  [false = fail]
    */
    function get_pointLayers($sql){

        $stmt = $this->db_connection->prepare('' . $sql . '');
        $result = $stmt->execute();

        if ($result) {
            //$return = array();
            while ($row = $stmt->fetch()) {
                echo $row[0] . "!! ";
                echo $row[1] . "!! ";
                echo $row[2] . "!! ";
                echo $row[3] . "!! ";
                echo ";";
            }
            return true;
        } else
            return false;

    }

	//Generates the quartiles for the legend break points. Currently hard coded to ntile(5) which is a quintile break points values. Set the value to number of points the legend should be broken into. 
	/**
    * @param string $tableName
    * @param string $publiccolumn
    * @param string $privatecolumn
    * @param string $bikecolumn
    * @param string $walkcolumn
    *
    * @return bool  [false = fail]
    */
    function get_legend_values($tableName, $publiccolumn, $privatecolumn, $bikecolumn, $walkcolumn){
        $stmt = $this->db_connection->prepare('SELECT max(fcavalue)*1000 as quantile_val, category  
                from (select "webmap".' . $tableName . '.' . $publiccolumn . ' as fcavalue , ntile(5) over (order by "webmap".' . $tableName . '.' . $publiccolumn . ') as category 
                from "webmap".' . $tableName . ' where "webmap".' . $tableName . '.' . $publiccolumn . ' > 0 order by "webmap".' . $tableName . '.' . $publiccolumn . ' asc ) as categorise
                group by category
                order by quantile_val asc, category asc;');
        $result = $stmt->execute();

        if ($result) {
            $return = array();
            while ($legendval = $stmt->fetch()) {
                $return[] = $legendval['quantile_val'];
            }
            return $return;
        } else
            return false;
    }

	//Returns an array of ages used within the calculation from the odmatrices details table (used for data purposes ie. use values in calculation). 
	/**
    * @param string $table
    *
    * @return array|bool  [false = fail]
    */
    function get_ages($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $ages = $stmt->fetch();
            $return = $ages['agerange'];
            return $return;
        } else
            return 'no age groups found';
    }

	//Echos a string of ages used within the calculation from the odmatrices details table and displays them as a string (used for display purposes ie. display names included in calculation). 
	/**
    * @param string $table
    *
    * @return echo string|bool  [false = fail]
    */
    function get_population_ages($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['agerange'];
			//Format string. 
            $returnnospace = str_replace(",", " / ", $return);
            $returnagereplace = str_replace("age", "ages ", $returnnospace);
            $returntoreplace = str_replace("to", " to ", $returnagereplace);
            echo $returntoreplace;
        } else
            return 'no age groups found';
    }

	//Returns a date string of the date and time the table was generated used within the calculation from the odmatrices details table (used for data purposes ie. use values in calculation). 
	/**
    * @param string $table
    *
    * @return string|bool  [false = fail]
    */
    function get_datetime_return($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select to_char(datetime, \'HH24:MI:SS     dd-MON-yyyy\') as datetime from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['datetime'];
            return $return;
        } else
            return 'no dates found';
    }

	//Echos a date string of the date and time the table was generated - used within the calculation from the odmatrices details table and displays them as a string (used for display purposes ie. display names included in calculation). 
	/**
    * @param string $table
    *
    * @return echo string|bool  [false = fail]
    */
    function get_datetime($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select to_char(datetime, \'HH24:MI:SS     dd-MON-yyyy\') as datetime from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['datetime'];
            echo $return;
        } else
            echo 'no dates found';

    }
	
	
	//Returns a date string of the supply table date, used within the calculation from the odmatrices details table (used for data purposes ie. use values in calculation). 
	/**
    * @param string $table
    *
    * @return string|bool  [false = fail]
    */
    function get_supply_date_return($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select to_char(supplytabledate, \'dd-mm-yyyy\') as supplytabledate from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['supplytabledate'];
            return $return;
        } else
            return 'no dates found';
    }
	
	//Echos a date sstring of the supply table date used within the calculation from the odmatrices details table and displays them as a string (used for display purposes ie. display names included in calculation). 
	/**
    * @param string $table
    *
    * @return echo string|bool  [false = fail]
    */
    function get_supply_date($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select to_char(supplytabledate, \'dd-mm-yyyy\') as supplytabledate from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['supplytabledate'];
            echo $return;
        } else
            echo 'no dates found';
    }

	//Returns a supply table version string, used within the calculation from the odmatrices details table (used for data purposes ie. use values in calculation). 
	/**
    * @param string $table
    *
    * @return string|bool  [false = fail]
    */
    function get_supply_version_return($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select supplytableversion from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['supplytableversion'];
            return $return;
        } else
            return 'no dates found';
    }

	//Echos a supply table version string used within the calculation from the odmatrices details table and displays them as a string (used for display purposes ie. display names included in calculation). 
	/**
    * @param string $table
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_supply_version($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select supplytableversion from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['supplytableversion'];
            echo $return;
        } else
            echo 'no dates found';
    }

	//Returns a road network version string, used within the calculation from the odmatrices details table (used for data purposes ie. use values in calculation). 
	/**
    * @param string $table
    *
    * @return string|bool  [false = fail]
    */
    function get_roadnetwork_date_return($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select to_char(roadnetworkdate, \'dd-MON-yyyy\') as roadnetworkdate from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['roadnetworkdate'];
            return $return;
        } else
            return 'no dates found';
    }
	
	//Echos a road network verison string used within the calculation from the odmatrices details table and displays them as a string (used for display purposes ie. display names included in calculation). 
	/**
    * @param string $table
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_roadnetwork_date($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select to_char(roadnetworkdate, \'dd-MON-yyyy\') as roadnetworkdate from "odmatricesdetails".odmatricesdetails where "odmatricesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['roadnetworkdate'];
            echo $return;
        } else
            echo 'no dates found';
    }


	//Returns all details of a specific instance of the MM2SFCA.  
	/**
    * @param string $CalculationName
    *
    * @return string|bool  [false = fail]
    */
    function get_calculation_data($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT *,  from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $return = $stmt->fetch();
            return $return;
        } else
            return 'no calculation found';

    }

	//Returns the catchment size from a specific calculation.   
	/**
    * @param string $CalculationName
    *
    * @return string|bool  [false = fail]
    */
    function get_catchment($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['calculationcatchment'];
            return $return;
        } else
            return 'no age groups found';
    }

	//Echos the specific age ranges within a MM2SFCA calculation. 
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_calc_ages($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['agerange'];

            $returnnospace = str_replace(",", " / ", $return);
            $returnagereplace = str_replace("age", "ages ", $returnnospace);
            $returntoreplace = str_replace("to", " to ", $returnagereplace);

            //var_dump($return);
            echo $returntoreplace;
        } else
            return 'no age groups found';
    }

	//Inserts calculation details into a table for a specific calculation performed using the MM2SFCA. 
	/**
    * @param string $CalculationName
    * @param string $ODTable
    * @param string $MeasurementType
    * @param string $CarPercentage
    * @param string $BusPercentage
    * @param string $BikePercentage
    * @param string $WalkPercentage
    * @param string $CatchmentSize
    * @param string $DecayType
    *
    * @return bool  [false = fail]
    */	
    function create_calculation_details($CalculationName, $ODTable, $MeasurementType, $CarPercentage, $BusPercentage, $BikePercentage, $WalkPercentage, $CatchmentSize, $DecayType){

		//Alter variable values for SQL 
        $ages = $this->get_ages($ODTable);
        $supplyver = $this->get_supply_version_return($ODTable);
        $supplycreationdate = $this->get_supply_date_return($ODTable);
        $roatnetwork = $this->get_roadnetwork_date_return($ODTable);
        list($facilityType1,$facilityType2,$demandType1,$demandType2, $demandType3, $initialCatchmentSize) = explode('_', $ODTable);
        $facilityType = $facilityType1 . " " . $facilityType2;
        $demandType = $demandType1 . " " . $demandType2 . " " . $demandType3;
        $CalcName = "'" . $CalculationName . "'";
        $MatrixTable = "'" . $ODTable . "'";
        $AgeRange = "'" . $ages . "'";
        $CalculationType = "'" . $MeasurementType . "'";
        $CarPer = "'" . $CarPercentage . "'";
        $BusPer = "'" . $BusPercentage . "'";
        $BikePer = "'" . $BikePercentage . "'";
        $WalkPer = "'" . $WalkPercentage . "'";
        $CalcCatchmentSize = "'" . $CatchmentSize . "'";
        $InitialSize = "'" . $initialCatchmentSize . "'";
        $Decay = "'" . $DecayType . "'";
        $SupplySide = "'" . $facilityType . "'";
        $DemandSide= "'" . $demandType . "'";
        $supplydate = "'" . $supplycreationdate . "'";
        $roadnetworkdate = "'" . $roatnetwork . "'";
        $supplyversion = "'" . $supplyver . "'";

		//Delete instance if it existed before the execution of the new MM2SFCA calculation with same parameters. 
        $stmt = $this->db_connection->prepare('DELETE FROM "odmatricesdetails".accessibilityresultsdetails WHERE calculationtablename = ' . $CalcName . ' and odname = ' . $MatrixTable . ';');
        $check = $stmt->execute();
		
        $stmt = $this->db_connection->prepare('INSERT INTO  "odmatricesdetails".accessibilityresultsdetails (calculationtablename, odname, agerange, calculationtype, privatepercent, publicpercent, bikepercent, walkpercent, initialcatchment, calculationcatchment, decaytype, facilitytype, demandtype, supplytabledate, roadnetworkdate, supplytableversion)
            Values(' . $CalcName . ',' . $MatrixTable . ',' . $AgeRange . ',' . $CalculationType . ',' . $CarPer . ',' . $BusPer . ',' . $BikePer . ',' . $WalkPer . ',' . $CalcCatchmentSize . ',' . $InitialSize . ',' . $Decay . ',' . $SupplySide . ',' . $DemandSide . ',' . $supplydate . ',' . $roadnetworkdate . ',' . $supplyversion . ');');
        $result = $stmt->execute();

        if ($result) {
            return true;
        } else
            return false;
    }


	//Echos private modal split percentage value.  
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_private_per($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['privatepercent'];
            echo $return;
        } else
            return 'no age groups found';
    }
	
	//Echos public modal split percentage value.  
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_public_per($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['publicpercent'];
            echo $return;
        } else
            return 'no age groups found';
    }

	//Echos cycling modal split percentage value.  
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_bike_per($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['bikepercent'];
            echo $return;
        } else
            return 'no age groups found';
    }

	//Echos walking modal split percentage value.  
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_walk_per($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['walkpercent'];
            echo $return;
        } else
            return 'no age groups found';
    }

	//Echo an origin to destination table name - ideally hide from users but useful for development. 
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_odname($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['odname'];
            echo $return;
        } else
            return 'no odname found';
    }

	//Echo the type of catchment used (distance or time based). 
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_calculationtype($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['calculationtype'];
            echo $return;
        } else
            return 'no type found';
    }

	//Echo the type of facility used within the calculation. 
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_facility_type($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['facilitytype'];
            echo $return;
        } else
            return 'no type found';
    }
	
	//Return a string for the facility type. 
	/**
    * @param string $CalculationName
    *
    * @return array|bool  [false = fail]
    */	
    function get_facility_type_return($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['facilitytype'];
            return $return;
        } else
            return 'no type found';
    }

	//Return a string for the catcgment and modify the string to include metres or minutes. 
	/**
    * @param string $CalculationName
    *
    * @return array|bool  [false = fail]
    */	
    function get_catchment_size($CalculationName, $measurement){
        $quotedCalculationName = "'" . $CalculationName . "'";
        //var_dump($quotedCalculationName);

        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $tablename = $catchment['calculationtablename'];
            list($decay_part1, $decay_part2, $catchementsize, $measurementtype, $modaltype) = explode('_', $tablename);

            if($measurement == "duration"){
                $typeaddon = " minutes";
            }else{
                $typeaddon = " metres";
            }
            $return = $catchementsize . $typeaddon;
            return $return;
        } else
            return 'no type found';
    }

	//Echo the supply table version date. 
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_calculation_supply_version($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['supplytableversion'];
            echo $return;
        } else
            return 'no age groups found';
    }

	//Echo the date of the calculation. 
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_calculation_supply_date($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['supplytabledate'];
            echo $return;
        } else
            return 'no age groups found';
    }

	//Echo the date of the road network. 
	/**
    * @param string $CalculationName
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_calculation_network_build_date($CalculationName){
        $quotedCalculationName = "'" . $CalculationName . "'";
        $stmt = $this->db_connection->prepare('SELECT * from "odmatricesdetails".accessibilityresultsdetails where calculationtablename = '.  $quotedCalculationName . ';');
        $result = $stmt->execute();
        if ($result) {
            $catchment = $stmt->fetch();
            $return = $catchment['roadnetworkdate'];
            echo $return;
        } else
            return 'no age groups found';
    }

	//Generate a supply table within the supplytables schema.  
	/**
    * @param string $uploadname
    * @param string $uploadicon
    *
    * @return bool  [false = fail]
    */	
    function create_supply_table($uploadname, $uploadicon){
        $stmt = $this->db_connection->prepare('drop table if exists supplynogeom.' . $uploadname . '_facilities');
        $stmt->execute();
		$stmt = $this->db_connection->prepare(
            "delete from odmatricesdetails.supplytablesdetails where tablename =  '" . $uploadname . "_facilities';");
		$stmt->execute();
		
        $stmt = $this->db_connection->prepare(
            'create table supplynogeom.' . $uploadname .  '_facilities (
            facility_id int,
            facilityname varchar(255),
            capacity int,
            postcode varchar(255),
            latitude float,
            longitude float
            );');
        $stmt->execute();
		
		$stmt = $this->db_connection->prepare(
			"insert into odmatricesdetails.supplytablesdetails (tablename, numberofservices, icon, datetime, supplytableversion)
			values('" . $uploadname . "_facilities', 176,'" . $uploadicon ."',CURRENT_TIMESTAMP,1);");
		$result = $stmt->execute();
	   
        if ($result) {
            return true;
        } else
            return 'no age groups found';
    }

	//Insert a row of supply table data into new supply table.   
	/**
    * @param string $uploadname
    * @param string $id
    * @param string $name
    * @param string $capacity
    * @param string $postcode
    * @param string $latitude
    * @param string $longitude
    *
    * @return bool  [false = fail]
    */
    function insert_row_supply_table($uploadname, $id, $name, $capacity, $postcode, $latitude, $longitude){
        $stmt = $this->db_connection->prepare(
            "INSERT INTO supplynogeom." . $uploadname .  "_facilities 
            (facility_id, facilityname, capacity, postcode, latitude, longitude)
            VALUES(" . $id . ",'". $name . "','" . $capacity . "','" . $postcode ."','" . $latitude . "','" . $longitude ."');");
        $result = $stmt->execute();
        if ($result) {
            return true;
        } else
            return 'no age groups found';
    }
	
	//Generates a supply table with geometry from postcodes.    
	/**
    * @param string $uploadname
    *
    * @return bool  [false = fail]
    */
	function create_supplytable_from_postcode($uploadname){
		$stmt = $this->db_connection->prepare(
            'drop table supplytables.' . $uploadname . ';');
		$stmt->execute();
		
		$stmt = $this->db_connection->prepare(
            'create table supplytables.' . $uploadname . '_facilities as (select facility_id as "id", b.postcode, facilityname, capacity, c.geom as geom
				from supplynogeom.' . $uploadname . '_facilities as b
				inner join supplynogeom.postcodegeom as c on 
				TRIM(lower(b.postcode)) = TRIM(lower(c.postcode)));');
		$result = $stmt->execute();
		
        if ($result) {
            return true;
        } else
            return 'unable to create supply table';

	}
	
	//Return a list of supply tables in decenting order of date and time they were uploaded.     
	/**
    * @return array|bool  [false = fail]
    */
	function get_supply_in_date_order_recent()
    {
        $stmt = $this->db_connection->prepare('SELECT * FROM "odmatricesdetails".supplytablesdetails order by datetime desc;' );
        $result = $stmt->execute();
        if ($result) {
            $return = array();
            while ($table = $stmt->fetch()) {
                $return[] = $table['tablename'];
            }
            return $return;
        } else
            return false;

    }

	//Echo the number of supply point locations from a supply table.    
	/**
    * @param string $table
    *
    * @return echo|bool  [false = fail]
    */	
	function get_supply_count($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select * from "odmatricesdetails".supplytablesdetails where "supplytablesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['numberofservices'];
            echo $return;
        } else
            echo 'no dates found';
    }
	
	
	//Return the name of an icon or image used to represent the supply table as a string.   
	/**
    * @param string $table
    *
    * @return string|bool  [false = fail]
    */	
	function get_icon($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select * from "odmatricesdetails".supplytablesdetails where "supplytablesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['icon'];
            return $return;
        } else
            return 'no icon found';
    }

	//Echo the date and time of the supply table upload.   
	/**
    * @param string $table
    *
    * @return echo string|bool  [false = fail]
    */	
    function get_supplytable_datetime($table){
        $quotedtable = "'" . $table . "'";
        $stmt = $this->db_connection->prepare('select to_char(datetime, \'dd-MON-yyyy\') as datetime from "odmatricesdetails".supplytablesdetails where "supplytablesdetails".tablename = '.  $quotedtable . ';');
        $result = $stmt->execute();
        if ($result) {
            $times = $stmt->fetch();
            $return = $times['datetime'];
            echo $return;
        } else
            echo 'no dates found';

    }
	
}