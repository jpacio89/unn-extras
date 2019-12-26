import java.io.File;

public class Run {

	public static void main(String[] args) {
		int timeWindow = 1;
		// java -jar AudioAnalyzer.jar "/Users/joaocoelho/Documents/Work/UNN/unn-datasets/taylor-swift/samples" "TaylorSwift" "/Users/joaocoelho/Documents/Work/UNN/datasets/audio-baseline" "Baseline" > dataset.csv
		// File f = new File("/Users/joaocoelho/Documents/Work/UNN/unn-datasets/taylor-swift/samples");
		// "TaylorSwift"
    	int n = 0;
		for (int i = 0; i < args.length; i += 2) {
	    	int missingRows = 1000;
			File f = new File(args[i]);
			File[] children = f.listFiles();
		    if (children != null) {
		        for (File child : children) {
		        	if (!child.isDirectory() && child.getName().endsWith(".wav")) {
		        		// System.out.println(child.getAbsolutePath());
		        		
		        		DatasetBuilder builder = new DatasetBuilder();
		        		builder.init(child.getAbsolutePath(), timeWindow, args[i+1]);
		        		
		        		if (n == 0) {
		        			builder.printHeader();
		        		}
		        		
		        		missingRows -= builder.load(missingRows);
		        		n++;
		        	}
		        }
		    }
		}	
	}

}
