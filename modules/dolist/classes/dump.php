<?php 
/* 
* string dump ( mixed $var ) 
* 
* returns a string containing a HTML-table representation of $var 
* 
* (c) 2001-2002 Daniel Jaenecke < jaenecke[AT]gmx[DOT]li 
* 
* published under the terms of the Gnu Pblic License 
* [ http://www.gnu.org/copyleft/gpl.html#SEC1 ] 
* 
* have fun 
*/ 

function dumpVar ($var,$label="") { 

/* 
* patterns for output 
* customize these to change reperesentation of data 
*/ 
    $pattern_key = '<td nowarap bgcolor=eeeeee><span style=\'font-family: arial; font-size: 10pt; font-weight: bold;\'>%s</span></td>'; 
    $pattern_type = '<td nowrap bgcolor=DEE3ED><i>%s</i></td>'; 
    $pattern_value = '<td bgcolor=ffffff><font face=arial size=3>%s</font></td>'; 

/* 
*  handling non-arrays 
*/ 
    if ( !is_array ( $var ) ) { 
        switch ( gettype ( $var ) ) { 
            case 'string': 
                if ( empty ( $var ) ) { 
                    return ' '; 
                } 
                else { 
                    return sprintf ( '<code>%s</code>',  
htmlentities ( $var, ENT_COMPAT, 'UTF-8') ); 
                } 
                break; // string 

            case 'boolean': 
                if ( $var ) { 
                    return '<i>true</i>'; 
                } 
                else { 
                    return '<i>false</i>'; 
                } 
                break; // boolean 
                 
            case 'object': 
                return dump ( array ( 
                    'class'    => get_class ( $var ), 
                    'parent_class' => get_parent_class ( $var ), 
                    'methods'=>get_class_methods ( get_class (  
$var ) ), 
                    'attributes' =>    get_object_vars ( $var ) 
                    ) ); 
                break; // object 

            case 'resource': 
                return sprintf ( '%s (%s)', $var, get_resource_type  
( $var ) ); 
                break; // resource 
                 
            default: 
                return $var; 
                break; // default 
        } // switch gettype ( value ) 
    } // !is_array 
     
/* 
* generate output 
*/ 
    $out = '<table cellpadding=5 cellspacing=1 bgcolor=555555>'; 
	if(isset($label) and mb_strlen($label)) { $out .= '<tr><td colspan=5 bgcolor=eeeeee><em><strong>' . $label . '</strong></em></td></tr>'; }
     
    foreach ( $var as $key => $value ) { 
     
    // get type of current value 
        $type = mb_substr ( gettype ( $var[ $key ] ), 0, 3 ); 
         
    // determine size of value if available 
        if ( $type == 'arr' )  
            $type .= sprintf ( '(%s)', sizeof ( $var[ $key ] ) ); 
        elseif ( $type == 'str' ) 
            $type .= sprintf ( '(%s)', mb_strlen ( $var[ $key ] ) ); 

        $out .= sprintf (  
            '<tr>' .  
                $pattern_key . 
                $pattern_type . 
                $pattern_value . 
            '</tr>', 
            $key, $type, dump ( $value ) 
        ); 

    } // foreach 
    $out .= '</table>'; 
    return $out; 
} // function dump () 

?>