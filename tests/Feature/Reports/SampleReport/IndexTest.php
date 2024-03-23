<?php

namespace Tests\Feature\Reports\SampleReport;

use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 *
 */
class IndexTest extends TestCase
{
    /**
     * @var string
     */
    protected string $uri = 'reports/sample-report';

    protected mixed $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'web');
    }

    public function testEqualFilters()
    {
        DB::statement('
            CREATE TEMPORARY TABLE IF NOT EXISTS temporary_report_table AS
            SELECT
                "Blue" as string_field,
                12.34 as float_field,
                1 as integer_field,
                "2005-05-20 12:13:14" as datetime_field,
                "2005-05-20" as date_field
        ');

        $params = implode('&', [
            'filename=report.json',
            'filter[string_field]=blue',
            'filter[float_field]=12.34',
            'filter[integer_field]=1',
            'filter[date_field]=2005-05-20',
            'filter[datetime_field]=2005-05-20 12:13:14',
        ]);

        $response = $this->get($this->uri . '?' . $params);

        ray($response->content());

        $response->assertSuccessful();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'string_field' => 'Blue',
            'float_field' => 12.34,
            'integer_field' => 1,
            'date_field' => '2005-05-20T00:00:00.000000Z',
            'datetime_field' => '2005-05-20T12:13:14.000000Z',
        ]);
    }

    public function testNotEqualFilters()
    {
        DB::statement('
            CREATE TEMPORARY TABLE IF NOT EXISTS temporary_report_table AS
            SELECT
                "Blue" as string_field,
                12.34 as float_field,
                1 as integer_field,
                "2005-05-20 12:13:14" as datetime_field,
                "2005-05-20" as date_field
        ');

        $params = implode('&', [
            'filename=report.json',
            'filter[string_field_not_equal]=black',
            'filter[float_field_not_equal]=23.12',
            'filter[integer_field_not_equal]=2',
            'filter[date_field_not_equal]=2001-01-01',
            'filter[datetime_field_not_equal]=2001-01-01 12:00:00',
        ]);

        $response = $this->get($this->uri . '?' . $params);

        ray($response->content());

        $response->assertSuccessful();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'string_field' => 'Blue',
            'float_field' => 12.34,
            'integer_field' => 1,
            'date_field' => '2005-05-20T00:00:00.000000Z',
            'datetime_field' => '2005-05-20T12:13:14.000000Z',
        ]);
    }

    public function testAllFilters()
    {
        DB::statement('
            CREATE TEMPORARY TABLE IF NOT EXISTS temporary_report_table AS
            SELECT
                "Blue" as string_field,
                12.34 as float_field,
                1 as integer_field,
                "2005-05-20 12:13:14" as datetime_field,
                "2005-05-20" as date_field

            UNION ALL SELECT
                "Black" as string_field,
                23.45 as float_field,
                3 as integer_field,
                "2015-10-09 07:23:23" as datetime_field,
                "2015-10-09" as date_field

            UNION ALL SELECT
                "Red" as string_field,
                34.56 as float_field,
                3 as integer_field,
                "2024-02-22 09:30:00" as datetime_field,
                "2024-02-22" as date_field
        ');

        $params = implode('&', [
            'filter[string_field]=blue',
            'filter[string_field_in]=BLUE,BLACK',
            'filter[string_field_not_in]=BLACK,RED',
            'filter[string_field_between]=B,D',
            'filter[string_field_greater_than]=A',
            'filter[string_field_lower_than]=R',
            'filter[string_field_contains]=Black',

            'filter[float_field]=12.34',
            'filter[float_field_in]=12.34,23.45',
            'filter[float_field_not_in]=12.34,23.45',
            'filter[float_field_between]=20,30',
            'filter[float_field_greater_than]=20',
            'filter[float_field_lower_than]=30',
            'filter[float_field_is_null]=true',

            'filter[integer_field]=1',
            'filter[integer_field_in]=1,2',
            'filter[integer_field_not_in]=1,2',
            'filter[integer_field_between]=2,3',
            'filter[integer_field_greater_than]=1',
            'filter[integer_field_lower_than]=3',
            'filter[integer_field_is_null]=false',

            'filter[date_field]=1',
            'filter[date_field_in]=2005-05-20,2015-10-09',
            'filter[date_field_not_in]=2005-05-20,2024-02-22',
            'filter[date_field_between]=2005-05-20,2024-02-22',
            'filter[date_field_greater_than]=2005-05-20',
            'filter[date_field_lower_than]=2024-02-22',
            'filter[date_field_is_null]=no',

            'filter[datetime_field]=2005-05-20 12:13:14',
            'filter[datetime_field_in]=2005-05-20 12:13:14,2024-02-22 09:30:00',
            'filter[datetime_field_not_in]=2005-05-20 12:13:14,2015-10-09 07:23:23',
            'filter[datetime_field_between]=2005-05-20 12:13:14,2024-02-22 09:30:00',
            'filter[datetime_field_greater_than]=2005-05-20 12:13:14',
            'filter[datetime_field_lower_than]=2024-02-22 09:30:00',
            'filter[datetime_field_is_null]=yes',
        ]);

        $response = $this->get($this->uri . '?' . $params);

        ray($response->content());

        $response->assertSuccessful();
    }
}
