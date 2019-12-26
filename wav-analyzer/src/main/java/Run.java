import java.io.File;

public class Run {

	public static void main(String[] args) {
		int timeWindow = 1;
		//File f = new File("/Users/joaocoelho/Documents/Work/UNN/unn-datasets/taylor-swift/samples");
		File f = new File(args[0]);
		File[] children = f.listFiles();
	    if (children != null) {
	    	int n = 0;
	        for (File child : children) {
	        	if (!child.isDirectory() && child.getName().endsWith(".wav")) {
	        		// System.out.println(child.getAbsolutePath());
	        		
	        		DatasetBuilder builder = new DatasetBuilder();
	        		builder.init(child.getAbsolutePath(), timeWindow, "TaylorSwift");
	        		
	        		if (n == 0) {
	        			builder.printHeader();
	        		}
	        		
	        		builder.load();
	        		n++;
	        	}
	        }
	    }
	}

}
