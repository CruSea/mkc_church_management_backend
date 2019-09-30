<?php

namespace App\Imports;

use App\Member;
use Maatwebsite\Excel\Concerns\ToModel;

class MembersImport implements ToModel
{
    /**
    * @param array $row

    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Member([
            'user_id' =>$row[1],
            'full_name'  => $row[2],
            'photo_url' => $row[3],
            'application_type' => $row[4],
            'city' => $row[5],
            'phone_cell' => $row[6],
            'phone_work' => $row[7],
            'phone_home' => $row[8],
            'email' => $row[9],
            'birth_day' => $row[10],
            'occupation' => $row[11],
            'employment_place'=> $row[12],
            'employment_position' => $row[13],
            'gender' => $row[14],
            'nationality' => $row[15],
            'address' => $row[16],
            'salvation_date' => $row[17],
            'is_baptized' => $row[18],
            'baptized_date' => $row[19],
            'marital_status' => $row[20],
        ]);
    }
}
