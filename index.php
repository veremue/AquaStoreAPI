<?php

include_once('connect.php');

$data = json_decode(file_get_contents('php://input'), true);
error_log(print_r($data, true));

//$method = 'view_all';
//$method = $_POST['method'];

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

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($json_data);
    }
}

$conn = null;