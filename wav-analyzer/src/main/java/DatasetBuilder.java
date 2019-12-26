import java.io.File;

public class DatasetBuilder {
	String filePath;
	int timeWindowMs;
	
	WavFile wavFile;
	int channelCount;
	long sampleRate;
	
	String className;
	int frameCount;
	
	public DatasetBuilder() {
		
	}
	
	public void init(String filePath, int timeWindowMs, String className) {
		this.filePath = filePath;
		this.timeWindowMs = timeWindowMs;
		this.className = className;
		
		initAudio();
	}
	
	public void initAudio() {		
        try {
            this.wavFile = WavFile.openWavFile(new File(this.filePath));
            // this.wavFile.display();
            
            this.channelCount = this.wavFile.getNumChannels();
            this.sampleRate = this.wavFile.getSampleRate();
            
            this.frameCount = (int) (timeWindowMs * sampleRate / 1000.0);
		} 
		catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	public int load(int maxLineCount) {
		double[] buffer = new double[this.frameCount * this.channelCount];

        int framesRead;
        int lineCount = 0;
        
        try {
    	   do {
    		   framesRead = this.wavFile.readFrames(buffer, this.frameCount);
    		   String[] input = new String[this.frameCount + 1];
    		   int n = 0;
    		   
    		   if (maxLineCount >= 0 && lineCount >= maxLineCount) {
    			   break;
    		   }

               for (int s = 0 ; s < framesRead * this.channelCount; s += this.channelCount) {
            	   input[n] = Double.toString(buffer[s]);
            	   n++;
               }
               
               input[n] = this.className;
               
               if (framesRead == this.frameCount) {
            	   String row = String.join(",", input);
            	   System.out.println(row);
               }
               
               lineCount++;
            }
            while (framesRead != 0);
    	   
           wavFile.close();
           
		} catch (Exception e) {
			e.printStackTrace();
		}
        
        return lineCount;
	}
	
	public void printHeader() {
		for (int i = 0; i < this.frameCount; ++i) {
			System.out.print(String.format("t[%d],", i));
		}
		System.out.println("class");
	}
	
	public int frameCount() {
		return this.frameCount;
	}
}
