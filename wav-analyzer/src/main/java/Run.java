import java.io.File;

public class Run {

	public static void main(String[] args) {
		DatasetBuilder builder = new DatasetBuilder();
		builder.init("/Users/joaocoelho/Documents/Work/UNN/unn-datasets/taylor-swift/samples/interview-01.wav", 1, "TaylorSwift");
		builder.load();
	}

}
