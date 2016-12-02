<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Topsis
{
	/**
	 * [$kriteria description]
	 * @var [type]
	 */
	private $kriteria;
	
	/**
	 * [$alternatif description]
	 * @var [type]
	 */
	private $alternatif;
	
	/**
	 * [$bobot description]
	 * @var [type]
	 */
	private $bobot;

	/**
	 * [pembagi description]
	 * @param  [type] $kriteria [description]
	 * @return [type]           [description]
	 */
	public function pembagi($kriteria)
	{
		$result = 0;
		foreach ($this->alternatif as $a)
		{
			foreach ($a['kriteria'] as $ak)
			{
				if ($ak['nama'] == $kriteria)
				{
					$result = $result + ($ak['nilai']*$ak['nilai']);
				}
			}
		}
		return sqrt($result);
	}

	/**
	 * [bobot_kriteria description]
	 * @param  [type] $kriteria [description]
	 * @return [type]           [description]
	 */
	public function bobot_kriteria($kriteria)
	{
		foreach ($this->bobot as $b)
		{
			if ($b['kriteria'] == $kriteria)
			{
				$result = $b['bobot'];
			}
		}
		return $result;
	}


	/**
	 * [a_max description]
	 * @param  [type] $kriteria      [description]
	 * @param  [type] $kriteria_tipe [description]
	 * @return [type]                [description]
	 */
	public function a_max($kriteria, $kriteria_tipe)
	{
		$result_array = array();
		foreach ($this->alternatif as $a)
		{
			foreach ($a['kriteria'] as $ak)
			{
				if ($ak['nama'] == $kriteria)
				{
					$result_array[] = ($ak['nilai'] / $this->pembagi($ak['nama']))*$this->bobot_kriteria($ak['nama']);
				}
			}
		}

		if ($kriteria_tipe == "cost")
		{
			$result = min($result_array);
		}
		elseif ($kriteria_tipe == "benefit")
		{
			$result = max($result_array);
		}
		return $result;
	}

	/**
	 * [a_min description]
	 * @param  [type] $kriteria      [description]
	 * @param  [type] $kriteria_tipe [description]
	 * @return [type]                [description]
	 */
	public function a_min($kriteria, $kriteria_tipe)
	{
		$result_array = array();
		foreach ($this->alternatif as $a)
		{
			foreach ($a['kriteria'] as $ak)
			{
				if ($ak['nama'] == $kriteria)
				{
					$result_array[] = ($ak['nilai'] / $this->pembagi($ak['nama']))*$this->bobot_kriteria($ak['nama']);
				}
			}
		}

		if ($kriteria_tipe == "cost")
		{
			$result = max($result_array);
		}
		elseif ($kriteria_tipe == "benefit")
		{
			$result = min($result_array);
		}
		return $result;
	}

	/**
	 * [d_max description]
	 * @param  [type] $alternatif [description]
	 * @return [type]             [description]
	 */
	public function d_max($alternatif)
	{
		$result = 0;
		foreach ($this->alternatif as $a)
		{
			if ($a['nama'] == $alternatif)
			{
				foreach ($a['kriteria'] as $ak)
				{
					foreach ($this->kriteria as $k)
					{
						if ($ak['nama'] == $k['nama'])
						{
							$a_max = $this->a_max($k['nama'], $k['tipe']);
							$terbobot = ($ak['nilai'] / $this->pembagi($ak['nama']))*$this->bobot_kriteria($ak['nama']);
							$h = ($a_max - $terbobot)*($a_max - $terbobot);
							$result = $result + $h;
						}
					}
				}
			}
		}
		return sqrt($result);
	}

	/**
	 * [d_min description]
	 * @param  [type] $alternatif [description]
	 * @return [type]             [description]
	 */
	public function d_min($alternatif)
	{
		$result = 0;
		foreach ($this->alternatif as $a)
		{
			if ($a['nama'] == $alternatif)
			{
				foreach ($a['kriteria'] as $ak)
				{
					foreach ($this->kriteria as $k)
					{
						if ($ak['nama'] == $k['nama'])
						{
							$a_max = $this->a_min($k['nama'], $k['tipe']);
							$terbobot = ($ak['nilai'] / $this->pembagi($ak['nama']))*$this->bobot_kriteria($ak['nama']);
							$h = ($a_max - $terbobot)*($a_max - $terbobot);
							$result = $result + $h;
						}
					}
				}
			}
		}
		return sqrt($result);
	}

	/**
	 * [v description]
	 * @return [type] [description]
	 */
	public function v()
	{
		$result = array();
		foreach ($this->alternatif as $a)
		{
			$alternatif = $a['nama'];
			$v = $this->d_min($a['nama']) / ($this->d_max($a['nama'])+$this->d_min($a['nama']));
			$result[] = array('nama'	=> $alternatif
				,'v'		=> $v);
		}

    // Obtain a list of columns
		foreach ($result as $key => $row) {
			$mid[$key]  = $row['v'];
		}

    // Sort the data with mid descending
    // Add $result as the last parameter, to sort by the common key
		array_multisort($mid, SORT_DESC, $result);

		return $result;
	}

	/**
	 * [run description]
	 * @param  [type] $p_kriteria   [description]
	 * @param  [type] $p_alternatif [description]
	 * @param  [type] $p_bobot      [description]
	 * @return [type]               [description]
	 */
	public function run($p_kriteria, $p_alternatif, $p_bobot)
	{
		$this->kriteria = $p_kriteria;
		$this->alternatif = $p_alternatif;
		$this->bobot = $p_bobot;

		$result = "
		<table class='table'>
			<tr>
				<td></td>
				";
				foreach ($this->kriteria as $k)
				{
					$result .= "
					<td>".$k['nama']." (".$k['tipe'].")</td>
					";
				}
				$result .= "
			</tr>
			";
			foreach ($this->alternatif as $a)
			{
				$result .= "
				<tr>
					<td>".$a['nama']."</td>
					";
					foreach ($a['kriteria'] as $ak)
					{
						$result .= "
						<td>".$ak['nilai']."</td>
						";
					}
					$result .= "
				</tr>
				";
			}

		// BOBOT
			$result .= "
			<tr>
				<td>Bobot</td>
				";
				foreach ($this->kriteria as $k)
				{
					$result .= "
					<td>".$this->bobot_kriteria($k['nama'])."</td>
					";
				}
				$result .= "
			</tr>
			";

		// PEMBAGI
			$result .= "
			<tr>
				<td>Pembagi</td>
				";
				foreach ($this->kriteria as $k)
				{
					$result .= "
					<td>".$this->pembagi($k['nama'])."</td>
					";
				}
				$result .= "
			</tr>
			";

		// TERNORMALISASI
			foreach ($this->alternatif as $a)
			{
				$result .= "
				<tr>
					<td>Ternormalisasi</td>
					";
					foreach ($a['kriteria'] as $ak)
					{
						$result .= "
						<td>".$ak['nilai'] / $this->pembagi($ak['nama'])."</td>
						";
					}
					$result .= "
				</tr>
				";
			}

		// TERBOBOT
			foreach ($this->alternatif as $a)
			{
				$result .= "
				<tr>
					<td>Terbobot</td>
					";
					foreach ($a['kriteria'] as $ak)
					{
						$result .= "
						<td>".($ak['nilai'] / $this->pembagi($ak['nama']))*$this->bobot_kriteria($ak['nama'])."</td>
						";
					}
					$result .= "
				</tr>
				";
			}

		// MAX
			$result .= "
			<tr>
				<td>Max</td>
				";
				foreach ($this->kriteria as $k)
				{
					$result .= "
					<td>".$this->a_max($k['nama'], $k['tipe'])."</td>
					";
				}
				$result .= "
			</tr>
			";

		// MIN
			$result .= "
			<tr>
				<td>Min</td>
				";
				foreach ($this->kriteria as $k)
				{
					$result .= "
					<td>".$this->a_min($k['nama'], $k['tipe'])."</td>
					";
				}
				$result .= "
			</tr>
			";

			$result .= "
		</table>
		";

		// D MAX
		$result .= "
		<br>
		<table class='table'>
			<tr>
				<td></td>
				<td>D Max</td>
				<td>D Mix</td>
				<td>V</td>
				";
				$result .= "
			</tr>
			";
			foreach ($this->alternatif as $a)
			{
				$result .= "
				<tr>
					<td>".$a['nama']."</td>
					<td>".$this->d_max($a['nama'])."</td>
					<td>".$this->d_min($a['nama'])."</td>
					<td>".$this->d_min($a['nama']) / ($this->d_max($a['nama'])+$this->d_min($a['nama']))."</td>
				</tr>
				";
			}

			$result .= "
		</table>
		";

		$result .= "
		Hasilnya:
		<ol>";
			foreach ($this->v() as $v)
			{
				$result .= "
				<li>".$v['nama']." | ".$v['v']."</li>
				";
			}
			$result .= "
		</ol>
		";

		return $result;
	}
}
