<?php

namespace App\Charts;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use marineusde\LarapexCharts\Charts\BarChart as OriginalBarChart;
use marineusde\LarapexCharts\Options\XAxisOption;

/**
 * Class ObservationsPerMonthChart
 *
 * This class is responsible for building a bar chart that represents the number of observations per month.
 * The chart includes deep sky observations and comet observations.
 */
class ObservationsPerMonthChart
{
    /**
     * Builds a bar chart of observations per month for a given user.
     *
     * This method retrieves the count of comet observations and deep sky observations made by the user for each month,
     * and stores them in two separate arrays. It then creates a bar chart with these data, sets the title, subtitle, colors,
     * and other properties of the chart, and returns it.
     *
     * The method uses raw SQL queries to retrieve the count of observations from the 'cometobservations' and 'observations' tables.
     * The queries group the observations by month and count them.
     *
     * The method then initializes two arrays with 12 zeros (representing the 12 months of the year), and populates them with the
     * count of observations for each month.
     *
     * Finally, the method creates a new OriginalBarChart object, sets its properties, and adds the count of deep sky and comet observations
     * as data series. The x-axis labels are set to the names of the months.
     *
     * @param  User  $user  The user for whom the chart is to be built.
     * @return OriginalBarChart The built bar chart.
     */
    public function build(User $user): OriginalBarChart
    {
        $cometobservations = DB::connection('mysqlOld')->select('select MONTH(date) as month,count(*) as cnt from cometobservations where observerid="'.$user->username.'" group by MONTH(date);');
        $deepskyobservations = DB::connection('mysqlOld')->select('select MONTH(date) as month,count(*) as cnt from observations where observerid="'.$user->username.'" group by MONTH(date);');

        $comets = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $deepsky = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        for ($i = 0; $i < count($cometobservations); $i++) {
            $comets[$cometobservations[$i]->month - 1] = $cometobservations[$i]->cnt;
        }

        for ($i = 0; $i < count($deepskyobservations); $i++) {
            $deepsky[$deepskyobservations[$i]->month - 1] = $deepskyobservations[$i]->cnt;
        }

        return (new OriginalBarChart)
            ->setTitle(__('Number of observations per month: ').$user->name)
            ->setSubtitle(__('Source: ').config('app.old_url'))
            ->setColors(['#00E396', '#feb019'])
            ->addData(__('Deepsky'), $deepsky)
            ->addData(__('Comets'), $comets)
            ->setXAxisOption(new XAxisOption([__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December')]))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setStacked(true)
            ->setToolbar(true);
    }
}
