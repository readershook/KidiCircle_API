<?php

class ffmpeg
{
    public function merge($data)
    {
        if(!empty($data["resource_id"])){
            $i = 1;
            $mp4files = array();
            $storage_path = $data["storage_path"];
            $directory = "$storage_path/app/".$data["resource_id"];
            do {
                $filepath = $directory."/".$i.".mp3";
                // echo $filepath."\n";
                if(file_exists($filepath)) {
                    // Resize video to landscape
                    $command = "/usr/bin/ffmpeg -y -i $directory/$i.jpg -vf scale=640:360 $directory/temp$i.jpg";
                    exec($command);
                    // echo $command."\n";exit;
                    // create video with no audio
                    $command = "/usr/bin/ffmpeg -y -loop 1 -i $directory/temp$i.jpg -c:V libx264 -t 120 -pix_fmt yuv420p $directory/temp$i.mp4";
                    shell_exec(escapeshellcmd($command));
                    // echo $command."\n";exit;
                    // Add audio to video
                    $command = "/usr/bin/ffmpeg -y -i $directory/temp$i.mp4 -i $directory/$i.mp3 -map 0:v -map 1:a -c:v copy -strict -2 -shortest $directory/$i.mp4";
                    shell_exec(escapeshellcmd($command));
                    // echo $command."\n";exit;

                    // $filepath = $directory."/temp".$i.".mp4";
                    // $command = "/usr/bin/ffmpeg -y -i $directory/temp2$i.mp4 -vf scale=640:360 -strict -2 $directory/$i.mp4";
                    // shell_exec(escapeshellcmd($command));
                    // echo $command."\n";exit;
                    // audio concat
                    //ffmpeg -i input1.wav -i input2.wav -i input3.wav -i input4.wav \
                    // -filter_complex '[0:0][1:0][2:0][3:0]concat=n=4:v=0:a=1[out]' \
                    // -map '[out]' output.wav
                    // audio length in seconds
                    // ffprobe -show_streams -select_streams a -v quiet /home/vipul/project/Kidi_API/storage/app/63976a1a98f31/1.mp3 | grep "duration=" | cut -d '=' -f 2
                    // shell_exec(escapeshellcmd($command));
                    // unlink($filepath);
                    unlink("$directory/temp$i.jpg");
                    unlink("$directory/temp$i.mp4");
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
                $concatvideoorder = "";
                foreach ($mp4files as $key => $value) {
                    // $command .= "[$key:v] [$key:a] ";
                    $command .= "[$key]scale=640:360:force_original_aspect_ratio=decrease,pad=640:360:(ow-iw)/2:(oh-ih)/2,setsar=1[v$key];";
                    $concatvideoorder .= "[v$key][$key:a:0]";
                }
                $command .= "$concatvideoorder concat=n=$countMp4files:v=1:a=1 [v] [a]\" -map \"[v]\" -map \"[a]\" $directory/final.mp4";
                // echo "$command\n";exit;
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