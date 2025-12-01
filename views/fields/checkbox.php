<?php 

            $vals = !empty($row[$col]) ? explode(',' , $row[$col]) : [] ;
            $labels = []; 
            foreach($vals as $v){
                //options ma value exists thase to te label ma conver thayi jase
                if(isset($config['options'][$v])){
                     $labels[] = $config['options'][$v];
                }else{ //use value
                    $labels[] = $v;
                }
            }
            echo htmlspecialchars(implode(',' , $labels));
    ?>

  