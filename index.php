<?php
header('Content-Type: application/json; charset=utf-8');
include_once('connect.php');

$json_incoming = file_get_contents('php://input'); 
$postdata = json_decode($json_incoming, 1);
$method = $postdata['method'];

$json_data = array();

if(isset($method))
{
    if($method == 'view_all')
    {
        $query = "SELECT a.id,a.description,a.glass_type,a.size,a.shape,a.created_at,a.updated_at,count(f.id) as fish
        FROM aquaria AS a
        LEFT JOIN fish AS f on a.id = f.aquaria_id
        GROUP BY a.id";

        $stmt = $conn->query($query);
        while ($row = $stmt->fetch()) 
        {
            $json_data[] = array(
                'id' => $row['id'],
                'description' => $row['description'],
                'glass_type' => $row['glass_type'],
                'size' => $row['size'],
                'shape' => $row['shape'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'fish' => $row['fish']
            );
        }        
    }
    else if($method == 'create_aquaria')
    {
        $description = $postdata['description'];
        $glass_type = $postdata['glass_type'];
        $size = $postdata['size'];
        $shape = $postdata['shape'];

        $time = date('Y-m-d H:i:s');
        $sql = "INSERT INTO aquaria (description, glass_type, size, shape, created_at, updated_at)
                VALUES ('$description', '$glass_type' ,'$size', '$shape', '$time', '$time')";
                
        $conn->exec($sql);
    }

    else if($method == 'add_fish')
    {
        $species = $postdata['species'];
        $color = $postdata['color'];
        $number_of_fins = $postdata['number_of_fins'];
        $aquaria_id = $postdata['aquaria_id'];

        $proceed = 1;

        //Rule 1: Goldfish can't go in the same aquarium as guppies.
        if($species == 'Goldfish' || $species == 'Guppies')
        {
            $distinctfishes = array();

            $query = "SELECT DISTINCT species AS species 
            FROM fish
            WHERE aquaria_id = $aquaria_id";

            $stmt = $conn->query($query);
            while ($row = $stmt->fetch()) 
            {
                array_push($distinctfishes, $row['species']);
            }  

            if(count($distinctfishes))
            {
                if($species == 'Goldfish' && in_array('Guppies', $distinctfishes)) $proceed = 0;
                if($species == 'Guppies' && in_array('Goldfish', $distinctfishes)) $proceed = 0;
            }
        }

        //Rule 2: Fish with three fins or more don't go in aquariums of 75 litres or less.
        if($number_of_fins >= 3)
        {
            $query = "SELECT size
            FROM `aquaria`
            WHERE id = $aquaria_id";

            $stmt = $conn->query($query);
            $row = $stmt->fetch();
            if($row['size'] <= 75) $proceed = 0;
        }

        if($proceed)
        {
            $time = date('Y-m-d H:i:s');
            $sql = "INSERT INTO fish (species, color, number_of_fins, aquaria_id, created_at, updated_at)
                    VALUES ('$species', '$color' ,'$number_of_fins', '$aquaria_id', '$time', '$time')";
                    
            $conn->exec($sql);
    
            $json_data = array(
                'result' => 'success',
            );
        } 
        else
        {
            $json_data = array(
                'result' => 'error',
            ); 
        }       

    }
    
    else if($method == 'update_fish')
    {
        $species = $postdata['species'];
        $color = $postdata['color'];
        $number_of_fins = $postdata['number_of_fins'];
        $fish_id = $postdata['fish_id'];

        $time = date('Y-m-d H:i:s');
        $sql = "UPDATE fish 
        SET species = '$species', color = '$color', number_of_fins = '$number_of_fins',  updated_at = '$time'
        WHERE id = $fish_id";
                
        $conn->exec($sql);

        $json_data = array(
            'result' => 'success',
        );
    }

    else if($method == 'remove_fish')
    {
        $id = $postdata['id'];

        $time = date('Y-m-d H:i:s');
        $sql = "DELETE FROM fish WHERE id = $id";
                
        $conn->exec($sql);

        $json_data = array(
            'result' => 'success',
        );

    }

    else if($method == 'show_aquaria')
    {
        $id = $postdata['id'];

        $query = "SELECT id, species,	color, number_of_fins, 	aquaria_id, created_at, updated_at
        FROM `fish`
        WHERE aquaria_id = $id";

        $fish_array = array();

        $stmt = $conn->query($query);
        while ($row = $stmt->fetch()) 
        {
            $fish_array[] = array(
                'id' => $row['id'],
                'species' => $row['species'],
                'color' => $row['color'],
                'number_of_fins' => $row['number_of_fins'],
                'aquaria_id' => $row['aquaria_id'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            );
        }

        $query = "SELECT a.id,a.description,a.glass_type,a.size,a.shape,a.created_at,a.updated_at,count(f.id) as fish
        FROM aquaria AS a
        LEFT JOIN fish AS f on a.id = f.aquaria_id
        WHERE a.id = $id
        GROUP BY a.id";

        $stmt = $conn->query($query);
        while ($row = $stmt->fetch()) 
        {
            $json_data = array(
                'id' => $row['id'],
                'description' => $row['description'],
                'glass_type' => $row['glass_type'],
                'size' => $row['size'],
                'shape' => $row['shape'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'fish' => $row['fish'],
                'fish_array' => $fish_array
            );
        }
        
    }

    error_log(print_r(json_encode($json_data), 1));

    echo json_encode($json_data);
}
else
{
    echo 'Error occured';
}



$conn = null;