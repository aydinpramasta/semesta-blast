<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BlastEmailRecipients implements WithHeadingRow, SkipsEmptyRows
{
}
