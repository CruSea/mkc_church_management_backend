<?php

namespace App\Exports;

use App\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Member::select(
            'member_id',
            'full_name',
            'city',
            'sub_city',
            'wereda',
            'house_number',
            'church_group_place',
            'phone_cell',
            'email',
            'birth_day',
            'birth_place',
            'occupation',
            'education_level',
            'gender',
            'nationality',
            'address',
            'salvation_date',
            'salvation_church',
            'is_baptized',
            'baptized_date',
            'baptized_church',
            'marital_status',
            'have_family_fellowship',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_subcity',
            'emergency_contact_house_no',
            'remark'
            )->get();
    }

    public function headings(): array
    {
        return [
            'Member Id',
            'Full Name',
            'City',
            'Sub-city',
            'Wereda',
            'House Number',
            'Small Team',
            'Phone',
            'Email',
            'Birthday',
            'Birth Place',
            'Occupation',
            'Educational Level',
            'Gender',
            'Nationality',
            'Address',
            'Aalvation Date',
            'Salvation Church',
            'Is Baptized',
            'Baptism Date',
            'Baptism Church	',
            'Marital Status',
            'Have Family Fellowship',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Emergency Contact Subcity',
            'Emergency Contact House',
            'Remark'
        ];
    }
}
