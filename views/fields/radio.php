   <?php
        if(!empty($config['options']) && isset($config['options'][$row[$col]])){
            echo htmlspecialchars($config['options'][$row[$col]]);
        }else{
            echo htmlspecialchars($row[$col]);
        }

    ?>