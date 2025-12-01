 
    <?php
        if(!empty($row[$col])){
            echo date("d-m-y" , strtotime($row[$col]));
        }else{
            echo "-";
        }

    ?>