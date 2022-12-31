<?php

class ffmpeg
{
    public function merge($data)
    {
        if(!empty($data["resource_id"])){
            $i = 1;
            $mp4files = array();
            $directory = "/home/vipul/project/Kidi_API/storage/app/".$data["resource_id"];
            do {
                $filepath = $directory."/".$i.".mp3";
                // echo $filepath."\n";
                if(file_exists($filepath)) {
                    $command = "/usr/bin/ffmpeg -y -loop 1 -i $directory/$i.jpg -i $directory/$i.mp3 -shortest -acodec copy -vcodec mjpeg $directory/temp$i.mp4";
                    // echo $command."\n";exit;
                    shell_exec(escapeshellcmd($command));
                    $filepath = $directory."/temp".$i.".mp4";
                    $command = "/usr/bin/ffmpeg -y -i $filepath -vf scale=640:360 -strict -2 $directory/$i.mp4";
                    // echo $command."\n";exit;
                    shell_exec(escapeshellcmd($command));
                    unlink($filepath);
                    $filepath = $directory."/".$i.".mp4";
                    if(file_exists($filepath)) {
                        array_push($mp4files, $filepath);
                    }
                    $i++;
                }
                else
                    $i=-1;
                
            } while ($i>0);
            $countMp4files = count($mp4files);
            if($countMp4files>0) {
                $command = "/usr/bin/ffmpeg -y ";
                foreach ($mp4files as $key => $value) {
                    $command .= "-i $value ";
                }
                $command .= "-filter_complex \"";
                foreach ($mp4files as $key => $value) {
                    $command .= "[$key:v] [$key:a] ";
                }
                $command .= " concat=n=$countMp4files:v=1:a=1 [v] [a]\" -map \"[v]\" -map \"[a]\" $directory/final.mp4";
                // echo "$command\n";
                exec($command);
                foreach ($mp4files as $key => $value) {
                    unlink($value);
                }
            }
        }
    }
}

$ffmpeg = new ffmpeg();
$data = array();
$tempdata = explode("&",$argv[1]);
foreach ($tempdata as $key => $value) {
    $data[explode("=",$value)[0]] = explode("=",$value)[1];
}

switch ($data["action"]) {
    case 'merge':
        $ffmpeg->merge($data);
        break;
    default:
        break;
}