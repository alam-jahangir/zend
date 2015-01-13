<?php
namespace Application\Model;
 
class JqgridSearch
{
    
    public function constructWhere($s)
    {
        $query = "";
    	//['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc']
        $qopers = array(
    				  'eq'=>" = ",
    				  'ne'=>" <> ",
    				  'lt'=>" < ",
    				  'le'=>" <= ",
    				  'gt'=>" > ",
    				  'ge'=>" >= ",
    				  'bw'=>" LIKE ",
    				  'bn'=>" NOT LIKE ",
    				  'in'=>" IN ",
    				  'ni'=>" NOT IN ",
    				  'ew'=>" LIKE ",
    				  'en'=>" NOT LIKE ",
    				  'cn'=>" LIKE " ,
    				  'nc'=>" NOT LIKE " );
        if ($s) {
            $jsona = json_decode($s, true);
            if (is_array($jsona)) {
    			$gopr = $jsona['groupOp'];
    			$rules = $jsona['rules'];
                $i =0;
                foreach($rules as $key=>$val) {
                    $field = $val['field'];
                    $op = $val['op'];
                    $v = $val['data'];
    				if($v && $op) {
    	                $i++;
    					// toSql in this case is absolutley needed
    					$v = $this->toSql($field,$op,$v);
    					if ($i == 1) $query = " AND ";
    					else $query .= " " .$gopr." ";
    					switch ($op) {
    						// in need other thing
    					    case 'in' :
    					    case 'ni' :
    					        $query .= $field.$qopers[$op]." (".$v.")";
    					        break;
    						default:
    					        $query .= $field.$qopers[$op].$v;
    					}
    				}
                }
            }
        }
        return $query;
    }


	public function getStringForGroup( $group )
	{
		$i_ = '';
		$sopt = array('eq' => "=",'ne' => "<>",'lt' => "<",'le' => "<=",'gt' => ">",'ge' => ">=",'bw'=>" {$i_}LIKE ",'bn'=>" NOT {$i_}LIKE ",'in'=>' IN ','ni'=> ' NOT IN','ew'=>" {$i_}LIKE ",'en'=>" NOT {$i_}LIKE ",'cn'=>" {$i_}LIKE ",'nc'=>" NOT {$i_}LIKE ", 'nu'=>'IS NULL', 'nn'=>'IS NOT NULL');
		$s = "(";
		if( isset ($group['groups']) && is_array($group['groups']) && count($group['groups']) >0 )
		{
			for($j=0; $j<count($group['groups']);$j++ )
			{
				if(strlen($s) > 1 ) {
					$s .= " ".$group['groupOp']." ";
				}
				try {
					$dat = getStringForGroup($group['groups'][$j]);
					$s .= $dat;
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}
		}
		if (isset($group['rules']) && count($group['rules'])>0 ) {
			try{
				foreach($group['rules'] as $key=>$val) {
					if (strlen($s) > 1) {
						$s .= " ".$group['groupOp']." ";
					}
					$field = $val['field'];
					$op = $val['op'];
					$v = $val['data'];
					if( $op ) {
						switch ($op)
						{
							case 'bw':
							case 'bn':
								$s .= $field.' '.$sopt[$op]."'$v%'";
								break;
							case 'ew':
							case 'en':
								$s .= $field.' '.$sopt[$op]."'%$v'";
								break;
							case 'cn':
							case 'nc':
								$s .= $field.' '.$sopt[$op]."'%$v%'";
								break;
							case 'in':
							case 'ni':
								$s .= $field.' '.$sopt[$op]."( '$v' )";
								break;
							case 'nu':
							case 'nn':
								$s .= $field.' '.$sopt[$op]." ";
								break;
							default :
								$s .= $field.' '.$sopt[$op]." '$v' ";
								break;
						}
					}
				}
			} catch (Exception $e) 	{
				echo $e->getMessage();
			}
		}
		$s .= ")";
		if ($s == "()") {
			//return array("",$prm); // ignore groups that don't have rules
			return " 1=1 ";
		} else {
			return $s;;
		}
	}


    function toSql ($field, $oper, $val) {
    	// we need here more advanced checking using the type of the field - i.e. integer, string, float
    	switch ($field) {
    		case 'id':
    			return intval($val);
    			break;
    		case 'amount':
    		case 'tax':
    		case 'total':
    			return floatval($val);
    			break;
    		default :
    			//mysql_real_escape_string is better
    			if($oper=='bw' || $oper=='bn') return "'" . addslashes($val) . "%'";
    			else if ($oper=='ew' || $oper=='en') return "'%" . addcslashes($val) . "'";
    			else if ($oper=='cn' || $oper=='nc') return "'%" . addslashes($val) . "%'";
    			else return "'" . addslashes($val) . "'";
    	}
    }

    public function strip($value)
    {
    	if(get_magic_quotes_gpc() != 0)
      	{
        	if(is_array($value))  
    			if ( array_is_associative($value) )
    			{
    				foreach( $value as $k=>$v)
    					$tmp_val[$k] = stripslashes($v);
    				$value = $tmp_val; 
    			}				
    			else  
    				for($j = 0; $j < sizeof($value); $j++)
            			$value[$j] = stripslashes($value[$j]);
    		else
    			$value = stripslashes($value);
    	}
    	return $value;
    }
    
    
    public function array_is_associative ($array)
    {
        if ( is_array($array) && ! empty($array) )
        {
            for ( $iterator = count($array) - 1; $iterator; $iterator-- )
            {
                if ( ! array_key_exists($iterator, $array) ) { return true; }
            }
            return ! array_key_exists(0, $array);
        }
        return false;
    }
    
} 
